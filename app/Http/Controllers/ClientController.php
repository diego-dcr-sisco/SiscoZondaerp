<?php

namespace App\Http\Controllers;

use App\Models\DirectoryManagement;
use App\Models\DirectoryPermission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\ClientFile;
use App\Models\Customer;
use App\Models\DirectoryUser;
use App\Models\LineBusiness;
use App\Models\MIPFile;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\Service;
use App\Models\UserCustomer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    private $path = 'client_system/';
    private $mip_path = 'mip_directory/';
    private $reports_path = 'backups/reports/';
    private $dir_names = [];
    private $disk_type = 'google'; // Cambiar a 'google' o 'public' según necesites

    private $size = 50;

    private $mip_directories = [
        'MIP',
        'Contrato de servicio',
        'Justificación',
        'Datos de la empresa',
        'Certificación MIP',
        'Plano de ubicación de dispositivos',
        'Responsabilidades',
        'Plago objeto',
        'Calendarización de actividades',
        'Descripción de actividades POEs',
        'Métodos preventivos',
        'Métodos correctivos',
        'Información de plaguicidas',
        'Reportes',
        'Gráficas de tendencias',
        'Señaléticas',
        'Pago seguro'
    ];

    // Método helper para obtener el disco configurado
    private function getDisk()
    {
        return Storage::disk($this->disk_type);
    }

    // Método para listar directorios (compatible con Flysystem v3)
    private function listDirectories($path)
    {
        $disk = $this->getDisk();
        $contents = $disk->listContents($path, false);

        return $contents->filter(fn($item) => $item->isDir())
            ->map(fn($item) => $item->path())
            ->toArray();
    }

    // Método para listar archivos (compatible con Flysystem v3)
    private function listFiles($path)
    {
        $disk = $this->getDisk();
        $contents = $disk->listContents($path, false);

        return $contents->filter(fn($item) => $item->isFile())
            ->map(fn($item) => $item->path())
            ->toArray();
    }

    // Método para listar recursivamente
    private function listAll($path, $recursive = false)
    {
        $disk = $this->getDisk();
        return $disk->listContents($path, $recursive)->toArray();
    }

    public function localClientSystemFormat($local_data)
    {
        $data = [];
        foreach ($local_data as $d) {
            $data[] = [
                'name' => basename($d),
                'path' => $d,
            ];
        }
        return $data;
    }

    private function getPermissions($dirs)
    {
        $permissions = [];
        foreach ($dirs as $dir) {
            $permissions[] = [
                'dirId' => $dir->id,
                'users' => DirectoryUser::where('directory_id', $dir->id)->get()->pluck('user_id'),
            ];
        }
        return $permissions;
    }

    private function getBreadcrumb($path)
    {
        $breadcrump = [];
        $aux = '';
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (!empty($part)) {
                $breadcrump[] = $aux . $part;
                $aux .= ($part . '/');
            }
        }
        return $breadcrump;
    }

    private function flattenArray(array $array): array
    {
        return array_merge(...$array);
    }

    private function uniqueArray($items)
    {
        $uniqueItems = array_unique(
            array_map(
                function ($item) {
                    return serialize($item);
                },
                $items
            )
        );

        return array_map(
            function ($item) {
                return unserialize($item);
            },
            $uniqueItems
        );
    }

    private function filterFiles($id, $date, $filesArray)
    {
        $results = [];
        $date = str_replace("-", "", $date);
        foreach ($filesArray as $file) {
            $fileParts = explode('_', $file['name']);
            if (count($fileParts) == 3) {
                $fileDate = $fileParts[0];
                $fileId = explode('.', $fileParts[2])[0];
                $dateMatches = ($date == null || $fileDate == $date);
                $idMatches = ($id == null || $fileId == $id);

                if ($dateMatches && $idMatches) {
                    $results[] = $file;
                }
            }
        }

        return $results;
    }

    private function getRootPath(string $path): string
    {
        $parts = explode('/', rtrim($path, '/'));
        return count($parts) > 1 ? $parts[0] . '/' . $parts[1] . '/' : $path . '/';
    }

    public function createMip(string $path)
    {
        foreach ($this->mip_directories as $name) {
            $folder_name = $path . '/' . $name;
            if (!$this->getDisk()->directoryExists($folder_name)) {
                $this->getDisk()->createDirectory($folder_name);
            }
        }
        return back();
    }

    public function index()
    {
        $path = $this->path;
        $mip_path = $this->mip_path;
        return view('client.index', compact('path', 'mip_path'));
    }

    public function directories(string $path)
    {
        $navigation = [
            'Carpetas' => route('client.system.index', ['path' => $this->path]),
            'Reportes' => route('client.reports')
        ];

        $mip_dirs = $mip_files = [];
        $disk = $this->getDisk();
        $dir_name = $this->mip_path . basename($path);

        // Usar métodos adaptados para Flysystem v3
        $local_dirs = $this->listDirectories($path);
        $local_files = $this->listFiles($path);

        sort($local_dirs);
        sort($local_files);

        $links = $this->getBreadcrumb($path);
        $user = User::find(auth()->user()->id);

        if ($disk->directoryExists($dir_name)) {
            $mip_dirs = $this->listDirectories($dir_name);
            $mip_files = $this->listFiles($dir_name);
        }

        $data = [
            'root_path' => $path,
            'directories' => $this->localClientSystemFormat($local_dirs),
            'files' => $this->localClientSystemFormat($local_files),
            'mip_directories' => $this->localClientSystemFormat($mip_dirs),
            'mip_files' => $this->localClientSystemFormat($mip_files)
        ];

        return view('client.directory.index', compact('data', 'links', 'user', 'navigation'));
    }

    public function mip(string $path)
    {
        $directories = $files = [];

        // Usar métodos adaptados
        $local_dirs = $this->listDirectories($path);
        $local_files = $this->listFiles($path);

        $links = $this->getBreadcrumb($path);

        $data = [
            'root_path' => $path,
            'directories' => $this->localClientSystemFormat($local_dirs),
            'files' => $this->localClientSystemFormat($local_files),
        ];

        return view('client.mip.index', compact('data', 'links'));
    }

    public function storeFile(Request $request)
    {
        $file_path = trim($request->input('path'), '/');
        $files = $request->file('files');

        if (!$files || count($files) === 0) {
            return redirect()->back()->withErrors(['files' => 'No files uploaded.']);
        }

        $disk = $this->getDisk();

        foreach ($files as $file) {
            if (!$file->isValid()) {
                return redirect()->back()->withErrors(['file' => 'Invalid file.']);
            }

            $filename = str_replace(' ', '_', $file->getClientOriginalName());
            $fullPath = $file_path . '/' . $filename;

            try {
                // Usar write para Flysystem v3
                $disk->write($fullPath, file_get_contents($file->getRealPath()));

            } catch (\Exception $e) {
                Log::error("Error uploading file {$filename}: " . $e->getMessage());
                return redirect()->back()->withErrors(['file' => "Error uploading file {$filename}"]);
            }
        }

        return back()->with('success', 'Files uploaded successfully!');
    }

    // ... (mantener los métodos storeSignature, processBase64Image, processUploadedImage iguales)
    public function storeSignature(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|exists:order,id',
                'name' => 'required|string|max:255',
                'signature' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $order = Order::findOrFail($request->input('order'));
            $name = $request->input('name');
            $base64Data = null;

            // Procesar firma digital
            if ($request->filled('signature')) {
                $base64Data = $this->processBase64Image($request->input('signature'));
            }

            // Procesar imagen subida
            if ($request->hasFile('image')) {
                $base64Data = $this->processUploadedImage($request->file('image'));
            }

            if (empty($base64Data)) {
                throw new \Exception('No se proporcionó ni firma ni imagen válida');
            }

            // Actualizar orden
            $order->update([
                'customer_signature' => $base64Data,
                'signature_name' => $name
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'signature_name' => $name,
                    'has_signature' => true,
                    'signature_source' => $request->filled('signature') ? 'drawing' : 'upload'
                ],
                'message' => 'Firma/imagen guardada correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa imagen en base64 (de la firma digital)
     */
    protected function processBase64Image($base64)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
            list(, $base64) = explode(',', $base64);
        }

        if (!base64_decode($base64, true)) {
            throw new \Exception('Formato base64 inválido');
        }

        return $base64;
    }

    /**
     * Procesa imagen subida via file input
     */
    protected function processUploadedImage($image)
    {
        if (!$image->isValid()) {
            throw new \Exception('Archivo de imagen inválido');
        }

        $imageContent = file_get_contents($image->getRealPath());
    }

    public function searchReport(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $sedes = Customer::where('general_sedes', '!=', 0)->get();
        $business_lines = LineBusiness::all();
        $section = 1;

        $order_id = $request->input('report');
        $customer_id = $request->input('sede');
        $serviceTerm = '%' . $request->input('service') . '%';
        $date = $request->input('date');
        $time = $request->input('time');
        $business_line_id = $request->input('business_line');
        $tracking_type = $request->input('tracking_type');

        $serviceIds = Service::where('name', 'LIKE', $serviceTerm)->get()->pluck('id');
        $orderServiceIds = OrderService::whereIn('service_id', $serviceIds)->get()->pluck('order_id');
        $orderBusinessIds = OrderService::where(
            'service_id',
            Service::where('business_line_id', $business_line_id)->get()->pluck('id')->toArray()
        )->get()->pluck('order_id');

        [$startDate, $endDate] = array_map(function ($d) {
            return Carbon::createFromFormat('d/m/Y', trim($d));
        }, explode(' - ', $date));

        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format(format: 'Y-m-d');

        $orders = Order::where('status_id', 5)->where('customer_id', $customer_id);

        if ($order_id) {
            $orders = $orders->where('id', $order_id);
        } else {
            $orders = $orders->where(function ($query) use ($order_id, $orderServiceIds, $orderBusinessIds, $startDate, $endDate, $time) {
                $query->whereBetween('programmed_date', [$startDate, $endDate])
                    ->orWhere('id', $order_id)
                    ->orWhereIn('id', $orderServiceIds)
                    ->orWhereIn('id', $orderBusinessIds)
                    ->orWhere('start_time', $time);
            });
        }

        $orders = $tracking_type ? $orders->whereNotNull('contract_id') : $orders->whereNull('contract_id');
        $orders = $orders->orderByRaw('signature_name IS NULL DESC')->paginate($this->size);
        return view('client.report.index', compact('user', 'orders', 'business_lines', 'sedes', 'section'));
    }

    function searchDirectories(Request $request, ?string $search = null, bool $exactMatch = false): array
    {
        try {
            $contain_root = str_starts_with($request->input('path'), $this->path);
            $search_path = $contain_root ? $request->input('path') : $this->path . $request->input('path');
            $search_path = Str::finish($search_path, '/');

            $disk = $this->getDisk();

            if (!$disk->directoryExists($search_path)) {
                return [];
            }

            $directories = $this->listDirectories($search_path);

            if (empty($search)) {
                return array_map(function ($dir) {
                    return [
                        'name' => basename($dir),
                        'path' => $dir,
                        'full_path' => $dir
                    ];
                }, $directories);
            }

            $searchTerm = Str::lower($search);
            $filtered = array_filter($directories, function ($dir) use ($searchTerm, $exactMatch) {
                $dirName = Str::lower(basename($dir));
                return $exactMatch ? $dirName === $searchTerm : Str::contains($dirName, $searchTerm);
            });

            return array_map(function ($dir) {
                return [
                    'name' => basename($dir),
                    'path' => $dir,
                    'full_path' => $dir
                ];
            }, array_values($filtered));

        } catch (\Exception $e) {
            Log::error("Folder search error: " . $e->getMessage());
            return [];
        }
    }

    public function searchBackupReport(Request $request)
    {
        $files = [];
        $business_lines = LineBusiness::all();
        $user = User::find(auth()->user()->id);
        $disk = $this->getDisk();
        $section = 2;

        $customer_id = $request->input('sede');
        $report_id = $request->input('report_id');
        $date = $request->input('date');

        $folder_name = Customer::find($customer_id)->name;

        // Obtener todos los directorios recursivamente
        $allContents = $disk->listContents($this->reports_path, true);
        $directories = $allContents->filter(fn($item) => $item->isDir())
            ->map(fn($item) => $item->path())
            ->toArray();

        $matchingDirectories = array_filter($directories, function ($dir) use ($folder_name) {
            return str_contains(strtolower($dir), strtolower($folder_name));
        });

        foreach ($matchingDirectories as $directory) {
            $dirFiles = $disk->listContents($directory, false)
                ->filter(fn($item) => $item->isFile())
                ->map(fn($item) => ['name' => basename($item->path()), 'path' => $item->path()])
                ->toArray();
            $files[] = $dirFiles;
        }

        $files = $this->filterFiles($report_id, $date, $this->uniqueArray($this->flattenArray($files)));

        return view('client.report.index', compact('user', 'files', 'folder_name', 'business_lines', 'section'));
    }

    public function downloadFile($path)
    {
        try {
            $disk = $this->getDisk();
            $decodedPath = urldecode($path);

            if ($disk->fileExists($decodedPath)) {
                $mimeType = $disk->mimeType($decodedPath);
                $fileContents = $disk->read($decodedPath);

                return response($fileContents)
                    ->header('Content-Type', $mimeType)
                    ->header('Content-Disposition', 'inline; filename="' . basename($decodedPath) . '"');
            }
            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while downloading the file.'], 500);
        }
    }

    public function managementDirectory(string $path)
    {
        $path = $path . '/';
        $dir_permissions = DirectoryPermission::where('path', $path)->get();

        if ($dir_permissions->isEmpty()) {
            $root = $this->getRootPath($path);
            $dir_permissions = DirectoryPermission::where('path', $root)->get();
        }

        foreach ($dir_permissions as $dir_per) {
            DirectoryManagement::updateOrCreate(
                [
                    'user_id' => $dir_per->user_id,
                    'path' => $path
                ],
                [
                    'is_visible' => DB::raw('NOT is_visible'),
                    'updated_at' => now()
                ]
            );
        }

        return back();
    }

    public function updateDirectory(Request $request)
    {
        $disk = $this->getDisk();
        $root_path = $request->input('root_path');
        $path = $request->input('path');
        $new_path = $root_path . '/' . $request->input('name');


        if ($disk->directoryExists($path)) {
            $disk->move($path, $new_path);
        }

        return back();
    }

    public function updateFile(Request $request)
    {
        $disk = $this->getDisk();

        $validated = $request->validate([
            'name' => 'required|string',
            'extension' => 'required|string',
            'path' => 'required|string',
            'root_path' => 'required|string',
        ]);

        $oldPath = rtrim($validated['path'], '/');
        $newFilename = $validated['name'] . '.' . $validated['extension'];
        $newPath = $validated['root_path'] . '/' . $newFilename;

        try {
            if (!$disk->fileExists($oldPath)) {
                return response()->json(['error' => 'El archivo no existe'], 404);
            }

            $disk->move($oldPath, $newPath);
            return back();

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al renombrar el archivo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyDirectory(string $path)
    {
        try {
            $disk = $this->getDisk();
            if ($disk->directoryExists($path)) {
                // Eliminar recursivamente
                $contents = $disk->listContents($path, true);
                foreach ($contents as $item) {
                    if ($item->isFile()) {
                        $disk->delete($item->path());
                    }
                }
                // Flysystem v3 no tiene deleteDirectory, así que eliminamos manualmente
                // Para Google Drive,可能需要 una solución diferente
                return back();
            }

            return response()->json(['error' => 'Directory not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the directory.'], 500);
        }
    }

    public function destroyFile(string $path)
    {
        try {
            $disk = $this->getDisk();
            if ($disk->fileExists($path)) {
                $disk->delete($path);
                return back();
            }

            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the file.'], 500);
        }
    }

    // ... (mantener los métodos permissions, copyDirectories, moveDirectories, reports iguales)
    public function permissions(Request $request)
    {
        $directoryId = $request->input('directory_id');
        $userIds = json_decode($request->input('selected_users'));
        $users = DirectoryUser::where('directory_id', $directoryId)->pluck('user_id')->toArray();

        //Elimina permisos
        $userIdstoDelete = array_diff($users, $userIds);
        foreach ($userIdstoDelete as $userId) {
            DirectoryUser::where('user_id', $userId)->where('directory_id', $directoryId)->delete();
        }

        // Agregar permiso
        $userIdstoAdd = array_diff($userIds, $users);
        foreach ($userIdstoAdd as $userId) {
            DirectoryUser::insert([
                'directory_idconsole.log(path);' => $directoryId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back();
    }

    public function copyDirectories(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'directories' => 'required|json'
        ]);

        $disk = Storage::disk('public');
        $destination = Str::finish($request->path, '/');
        $directories = json_decode($request->directories, true);
        $results = [];
        $allSuccess = true;

        foreach ($directories as $directory) {
            $source = Str::finish($directory, '/');
            $dirname = basename(rtrim($source, '/'));
            $target = $destination . $dirname;

            try {
                // Verificaciones iniciales
                if (!$disk->exists($source)) {
                    throw new \Exception('El directorio origen no existe');
                }
                if ($disk->exists($target)) {
                    throw new \Exception('El directorio destino ya existe');
                }

                // Crear directorio principal
                $disk->makeDirectory($target);

                // Copiar estructura completa
                $allContents = $disk->allDirectories($source);
                $allContents = array_merge($allContents, $disk->allFiles($source));

                foreach ($allContents as $item) {
                    $relativePath = Str::after($item, $source);
                    $newItemPath = $target . '/' . $relativePath;

                    // Crear subdirectorios primero
                    if ($disk->directoryExists($item)) {
                        if (!$disk->makeDirectory($newItemPath)) {
                            throw new \Exception("Fallo al crear subdirectorio: {$newItemPath}");
                        }
                    } else {
                        // Para archivos, asegurar que existe su directorio padre
                        $parentDir = dirname($newItemPath);
                        if (!$disk->exists($parentDir)) {
                            $disk->makeDirectory($parentDir);
                        }
                        $disk->copy($item, $newItemPath);
                    }
                }

                $results[$source] = [
                    'success' => true,
                    'message' => 'Directorio y subdirectorios copiados correctamente',
                    'new_path' => $target
                ];

            } catch (\Exception $e) {
                // Limpieza en caso de error
                if ($disk->exists($target)) {
                    $disk->deleteDirectory($target);
                }

                $results[$source] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error copiando {$source}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'results' => $results
        ]);
    }

    public function moveDirectories(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'directories' => 'required|json'
        ]);

        $disk = Storage::disk('public');
        $destination = Str::finish($request->path, '/');
        $directories = json_decode($request->directories, true);
        $results = [];
        $allSuccess = true;

        foreach ($directories as $directory) {
            $source = Str::finish($directory, '/');
            $dirname = basename(rtrim($source, '/'));
            $target = $destination . $dirname;

            try {
                // Verificar si el origen existe
                if (!$disk->exists($source)) {
                    $results[$source] = [
                        'success' => false,
                        'message' => 'El directorio origen no existe'
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Verificar si el destino existe
                if ($disk->exists($target)) {
                    $results[$source] = [
                        'success' => false,
                        'message' => 'El directorio destino ya existe'
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Mover el directorio
                $moved = $disk->move($source, $target);

                if ($moved) {
                    $results[$source] = [
                        'success' => true,
                        'message' => 'Directorio movido correctamente',
                        'new_path' => $target
                    ];
                } else {
                    throw new \Exception("Error al mover el directorio");
                }

            } catch (\Exception $e) {
                $results[$source] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error moving directory {$source}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'results' => $results
        ]);
    }

    // Funciones para los filtros de reportes 
    public function reports(Request $request)
    {
        //dd($request->all());
        $navigation = [
            'Carpetas' => route('client.system.index', ['path' => $this->path]),
            'Reportes' => route('client.reports')
        ];

        $user = User::find(auth()->user()->id);
        $business_lines = LineBusiness::all();
        $sedes = $user->role_id == 5
            ? $user->customers
            : Customer::where('general_sedes', '!=', 0)->orderBy('name', 'asc')->get();

        $query = Order::query();

        // Validar que se haya seleccionado una sede
        $filteredParams = $request->except('page');

        // Validar que se haya seleccionado al menos un filtro (excluyendo page)
        $has_orders = count($filteredParams) > 0;

        if ($has_orders) {
            if ($request->filled('sede')) {
                $query->where('customer_id', $request->input('sede'));
            }

            if ($request->filled('no_report')) {
                $query->where('folio', 'LIKE', '%-' . $request->no_report);
            }

            if ($request->filled('date_range')) {
                [$startDate, $endDate] = array_map(function ($d) {
                    return Carbon::createFromFormat('d/m/Y', trim($d));
                }, explode(' - ', $request->input('date_range')));

                $query->whereBetween('programmed_date', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ]);
            }

            if ($request->filled('service')) {
                $serviceName = '%' . $request->input('service') . '%';
                $serviceIds = Service::where('name', 'LIKE', $serviceName)->pluck('id');
                $orderIds = OrderService::whereIn('service_id', $serviceIds)->pluck('order_id');
                $query->whereIn('id', $orderIds);
            }

            if ($request->filled('has_signature')) {
                $query = $request->input('has_signature') == "yes" ?
                    $query->whereNotNull('customer_signature') :
                    $query->whereNull('customer_signature');
            }
        }

        $orders = $query->where('status_id', 5)->orderByRaw('signature_name IS NULL DESC')
            ->orderBy('programmed_date', 'desc')->paginate($this->size)->appends($request->query());

        return view('client.report.index', compact(
            'user',
            'orders',
            'business_lines',
            'sedes',
            'navigation',
            'has_orders'
        ));

    }

    public function listDirs(Request $request)
    {
        $input = trim($request->input('path', ''), '/');

        if (strpos($input, 'client_system/') === 0) {
            $subpath = substr($input, strlen('client_system/'));
        } else {
            $subpath = $input;
        }

        $disk = $this->getDisk();
        $basePath = $subpath !== '' ? "client_system/{$subpath}" : 'client_system';

        $list = function (string $path) use (&$list, $disk) {
            if (!$disk->directoryExists($path)) {
                return [];
            }

            $dirs = [];
            $contents = $disk->listContents($path, false);

            foreach ($contents as $item) {
                if ($item->isDir()) {
                    $rel = substr($item->path(), strlen('client_system/'));
                    $rel = ltrim($rel, '/');
                    $dirs[] = [
                        'name' => basename($item->path()),
                        'path' => $rel,
                        'children' => $list($item->path()),
                    ];
                }
            }
            return $dirs;
        };

        return response()->json($list($basePath));
    }

    // Método para cambiar entre discos (opcional)
    public function switchDisk($type)
    {
        $this->disk_type = in_array($type, ['public', 'google']) ? $type : 'public';
        return back()->with('success', 'Disk switched to ' . $this->disk_type);
    }

    public function storeDirectory(Request $request)
    {
        try {
            $request->validate([
                'folder_name' => 'required|string|max:255',
                'parent_path' => 'nullable|string',
                'is_mip' => 'nullable|boolean'
            ]);

            $folderName = $request->input('folder_name');
            $parentPath = $request->input('parent_path');
            $isMip = $request->input('is_mip', false);

            // Determinar la ruta base según el tipo
            $basePath = $isMip ? $this->mip_path : $this->path;

            // Construir la ruta completa
            $fullPath = $parentPath
                ? rtrim($parentPath, '/') . '/' . $folderName
                : rtrim($basePath, '/') . '/' . $folderName;

            $disk = $this->getDisk();

            // Verificar si la carpeta ya existe
            if ($disk->directoryExists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe'
                ], 409);
            }

            // Crear la carpeta
            if ($disk->makeDirectory($fullPath)) {
                // Si es una carpeta MIP, crear la estructura completa
                if ($isMip) {
                    $this->createMipStructure($fullPath);
                }

                return back()->with('success', 'Carpeta creada exitosamente');
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la carpeta'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating directory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea la estructura completa de carpetas MIP
     *
     * @param string $basePath
     * @return void
     */
    private function createMipStructure(string $basePath)
    {
        $disk = $this->getDisk();

        foreach ($this->mip_directories as $directory) {
            $folderPath = $basePath . '/' . $directory;
            if (!$disk->directoryExists($folderPath)) {
                $disk->makeDirectory($folderPath);
            }
        }
    }

    /**
     * Crea múltiples carpetas recursivamente
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDirectoriesRecursive(Request $request)
    {
        try {
            $request->validate([
                'folder_path' => 'required|string',
                'parent_path' => 'nullable|string',
                'is_mip' => 'nullable|boolean'
            ]);

            $folderPath = $request->input('folder_path');
            $parentPath = $request->input('parent_path');
            $isMip = $request->input('is_mip', false);

            $basePath = $isMip ? $this->mip_path : $this->path;
            $fullBasePath = $parentPath ?: $basePath;

            $folders = explode('/', trim($folderPath, '/'));
            $currentPath = rtrim($fullBasePath, '/');

            $createdFolders = [];

            foreach ($folders as $folder) {
                if (empty(trim($folder)))
                    continue;

                $currentPath .= '/' . $folder;
                $disk = $this->getDisk();

                if (!$disk->directoryExists($currentPath)) {
                    if ($disk->makeDirectory($currentPath)) {
                        $createdFolders[] = $currentPath;
                    } else {
                        throw new \Exception("Error al crear la carpeta: {$currentPath}");
                    }
                }
            }

            // Si es MIP, crear estructura en la última carpeta
            if ($isMip && !empty($createdFolders)) {
                $this->createMipStructure(end($createdFolders));
            }

            return response()->json([
                'success' => true,
                'message' => 'Carpetas creadas exitosamente',
                'created_folders' => $createdFolders,
                'final_path' => $currentPath
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating recursive directories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica si una carpeta existe
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function directoryExists(Request $request)
    {
        try {
            $request->validate([
                'folder_path' => 'required|string'
            ]);

            $folderPath = $request->input('folder_path');
            $disk = $this->getDisk();

            $exists = $disk->directoryExists($folderPath);

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'folder_path' => $folderPath
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDirectoryName(Request $request)
    {
        try {
            $request->validate([
                'current_path' => 'required|string',
                'new_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_\.]+$/',
                'is_mip' => 'nullable|boolean'
            ], [
                'new_name.regex' => 'El nombre solo puede contener letras, números, espacios, guiones, puntos y guiones bajos.',
                'new_name.max' => 'El nombre no puede exceder los 255 caracteres.'
            ]);

            $currentPath = $request->input('current_path');
            $newName = trim($request->input('new_name'));
            $isMip = $request->input('is_mip', false);

            $disk = $this->getDisk();

            // Verificar que la carpeta original existe
            if (!$disk->directoryExists($currentPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta no existe o no se puede acceder a ella'
                ], 404);
            }

            // Verificar que el nuevo nombre no sea igual al actual
            $currentName = basename($currentPath);
            if ($currentName === $newName) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nuevo nombre es igual al nombre actual'
                ], 422);
            }

            // Construir la nueva ruta
            $parentPath = dirname($currentPath);
            $newPath = $parentPath . '/' . $newName;

            // Verificar si ya existe una carpeta con el nuevo nombre
            if ($disk->directoryExists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una carpeta con ese nombre en esta ubicación'
                ], 409);
            }

            // Renombrar la carpeta
            $renamed = $disk->move($currentPath, $newPath);

            if ($renamed) {
                // Si es una carpeta MIP, también actualizar las referencias en la base de datos si es necesario
                if ($isMip) {
                    $this->updateMipReferences($currentPath, $newPath);
                }

                // Actualizar permisos si existen en la base de datos
                $this->updateDirectoryPermissions($currentPath, $newPath);

                Log::info("Carpeta renombrada: {$currentPath} -> {$newPath}");

                return response()->json([
                    'success' => true,
                    'message' => 'Carpeta renombrada exitosamente',
                    'old_path' => $currentPath,
                    'new_path' => $newPath,
                    'new_name' => $newName
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al renombrar la carpeta'
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error renaming directory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

}