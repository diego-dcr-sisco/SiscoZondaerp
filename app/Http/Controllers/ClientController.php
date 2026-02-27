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
    private function getDirectoriesInPath($path)
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
        $local_dirs = $this->getDirectoriesInPath($path);
        $local_files = $this->listFiles($path);

        sort($local_dirs);
        sort($local_files);

        $links = $this->getBreadcrumb($path);
        $user = User::find(auth()->user()->id);

        if ($disk->directoryExists($dir_name)) {
            $mip_dirs = $this->getDirectoriesInPath($dir_name);
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
        $local_dirs = $this->getDirectoriesInPath($path);
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
        $uploadedFiles = [];
        $skippedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                $errors[] = 'Archivo inválido: ' . $file->getClientOriginalName();
                continue;
            }

            $originalFilename = $file->getClientOriginalName();
            $filename = str_replace(' ', '_', $originalFilename);
            $fullPath = $file_path . '/' . $filename;

            try {
                // Verificar si el archivo ya existe
                if ($disk->fileExists($fullPath)) {
                    // Obtener timestamp para nombre único
                    $timestamp = time();
                    $pathInfo = pathinfo($filename);
                    $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
                    $basename = $pathInfo['filename'];

                    // Crear nuevo nombre con timestamp
                    $filename = $basename . '_' . $timestamp . $extension;
                    $fullPath = $file_path . '/' . $filename;

                    // Verificar nuevamente (por si acaso)
                    if ($disk->fileExists($fullPath)) {
                        $skippedFiles[] = $originalFilename . ' (ya existe)';
                        continue;
                    }
                }

                // Subir el archivo
                $disk->write($fullPath, file_get_contents($file->getRealPath()));
                $uploadedFiles[] = $filename;

            } catch (\Exception $e) {
                Log::error("Error uploading file {$filename}: " . $e->getMessage());
                $errors[] = "Error al subir {$originalFilename}: " . $e->getMessage();
            }
        }

        // Preparar mensaje de respuesta
        $messages = [];
        if (!empty($uploadedFiles)) {
            $messages[] = count($uploadedFiles) . ' archivo(s) subido(s) exitosamente';
        }
        if (!empty($skippedFiles)) {
            $messages[] = count($skippedFiles) . ' archivo(s) omitido(s) (ya existían)';
        }
        if (!empty($errors)) {
            return redirect()->back()->withErrors(['file' => implode(', ', $errors)]);
        }

        return back()->with('success', implode('. ', $messages));
    }

    // ... (mantener los métodos storeSignature, processBase64Image, processUploadedImage iguales)
    public function storeSignature(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|exists:order,id',
                'name' => 'required|string|max:1024',
                'signature' => 'nullable|string', // Firma en base64
                'image' => 'nullable|string' // Imagen en base64
            ]);

            $order = Order::findOrFail($request->input('order'));
            $name = $request->input('name');
            $base64Data = null;
            $signatureSource = null;

            // Procesar firma digital (viene como string base64)
            if ($request->filled('signature')) {
                $signatureSource = 'drawing';

                // Validar que sea un string base64 válido
                $signatureData = $request->input('signature');
                if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $signatureData)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de firma no válido. Debe ser base64 de imagen.'
                    ], 422);
                }

                $base64Data = $this->processBase64Image($signatureData);
            }

            // Procesar imagen subida en base64
            if ($request->filled('image') && !$base64Data) {
                $signatureSource = 'upload';

                // Validar que sea un string base64 válido
                $imageData = $request->input('image');
                if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $imageData)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de imagen no válido. Debe ser base64 de imagen.'
                    ], 422);
                }

                $base64Data = $this->processBase64Image($imageData);
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
                    'signature_source' => $signatureSource
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

    private function processBase64Image($base64Data)
    {
        try {
            // Separar el encabezado de los datos
            $exploded = explode(',', $base64Data);
            if (count($exploded) < 2) {
                throw new \Exception('Formato base64 no válido');
            }

            $imageData = base64_decode($exploded[1]);

            if ($imageData === false) {
                throw new \Exception('Error al decodificar base64');
            }

            // Obtener el tipo MIME del encabezado
            $mimeType = '';
            $header = $exploded[0];
            if (preg_match('/data:image\/(\w+);base64/', $header, $matches)) {
                $mimeType = $matches[1];
            }

            // Validar tamaño (máximo 5MB)
            $sizeInBytes = (int) (strlen(rtrim($base64Data, '=')) * 3 / 4);
            if ($sizeInBytes > 5 * 1024 * 1024) { // 5MB
                throw new \Exception('La imagen excede el tamaño máximo de 5MB');
            }

            // Para Laravel, puedes guardar como base64 directo en la base de datos
            // O si prefieres guardar como archivo:
            return $base64Data; // O procesar y guardar como archivo

            // Opcional: Guardar como archivo
            /*
            $fileName = 'signature_' . time() . '_' . uniqid() . '.' . $mimeType;
            $path = 'signatures/' . $fileName;

            // Guardar en storage
            Storage::disk('public')->put($path, $imageData);

            return $path; // Retornar ruta del archivo
            */

        } catch (\Exception $e) {
            throw new \Exception('Error procesando imagen: ' . $e->getMessage());
        }
    }
    /**
     * Procesa imagen en base64 (de la firma digital)
     */
    /*protected function processBase64Image($base64)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
            list(, $base64) = explode(',', $base64);
        }

        if (!base64_decode($base64, true)) {
            throw new \Exception('Formato base64 inválido');
        }

        return $base64;
    }*/

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

            $directories = $this->getDirectoriesInPath($search_path);

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

            if (!$disk->directoryExists($path)) {
                return response()->json(['error' => 'Directory not found.'], 404);
            }

            // Para Google Drive, usar una estrategia diferente
            if ($this->disk_type === 'google') {
                $this->deleteGoogleDriveDirectory($disk, $path);
            } else {
                // Para almacenamiento local
                $this->deleteLocalDirectory($disk, $path);
            }

            return back()->with('success', 'Directorio eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error deleting directory: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while deleting the directory.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un directorio de Google Drive recursivamente
     */
    private function deleteGoogleDriveDirectory($disk, string $path)
    {
        // Listar contenido recursivamente
        $contents = $disk->listContents($path, true)->toArray();

        // Separar archivos y directorios
        $files = [];
        $directories = [];

        foreach ($contents as $item) {
            if ($item->isFile()) {
                $files[] = $item->path();
            } elseif ($item->isDir()) {
                $directories[] = $item->path();
            }
        }

        // Eliminar primero todos los archivos
        foreach ($files as $file) {
            try {
                $disk->delete($file);
            } catch (\Exception $e) {
                Log::warning("No se pudo eliminar archivo: {$file} - " . $e->getMessage());
            }
        }

        // Ordenar directorios por profundidad (más profundo primero)
        usort($directories, function ($a, $b) {
            return substr_count($b, '/') - substr_count($a, '/');
        });

        // Eliminar directorios en orden
        foreach ($directories as $directory) {
            try {
                $disk->deleteDirectory($directory);
            } catch (\Exception $e) {
                Log::warning("No se pudo eliminar directorio: {$directory} - " . $e->getMessage());
            }
        }

        // Finalmente eliminar el directorio raíz
        $disk->deleteDirectory($path);
    }

    /**
     * Elimina un directorio local recursivamente
     */
    private function deleteLocalDirectory($disk, string $path)
    {
        $contents = $disk->listContents($path, true)->toArray();

        // Ordenar por profundidad en orden descendente
        usort($contents, function ($a, $b) {
            return substr_count($b->path(), '/') - substr_count($a->path(), '/');
        });

        // Eliminar archivos y directorios en orden inverso
        foreach ($contents as $item) {
            if ($item->isFile()) {
                $disk->delete($item->path());
            } elseif ($item->isDir()) {
                $disk->deleteDirectory($item->path());
            }
        }

        // Eliminar el directorio raíz
        $disk->deleteDirectory($path);
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

    /**
     * Eliminar múltiples carpetas de forma masiva
     */
    public function massDeleteDirectories(Request $request)
    {
        $request->validate([
            'directories' => 'required|string'
        ]);

        $disk = $this->getDisk();
        $directories = json_decode($request->input('directories'), true);
        $results = [];
        $allSuccess = true;
        $deletedCount = 0;

        foreach ($directories as $directory) {
            // Normalizar ruta
            $dirPath = trim($directory, '/');
            if (strpos($dirPath, 'client_system/') !== 0) {
                $dirPath = 'client_system/' . $dirPath;
            }

            try {
                // Verificar si el directorio existe
                if (!$disk->exists($dirPath)) {
                    $results[$directory] = [
                        'success' => false,
                        'message' => 'El directorio no existe'
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Eliminar el directorio y todo su contenido
                $deleted = $disk->deleteDirectory($dirPath);

                if ($deleted) {
                    $results[$directory] = [
                        'success' => true,
                        'message' => 'Directorio eliminado correctamente'
                    ];
                    $deletedCount++;
                    \Log::info("Directorio eliminado: {$dirPath}");
                } else {
                    throw new \Exception("Error al eliminar el directorio");
                }

            } catch (\Exception $e) {
                $results[$directory] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error deleting directory {$directory}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'deleted_count' => $deletedCount,
            'total' => count($directories),
            'results' => $results,
            'message' => $allSuccess 
                ? "Todos los directorios fueron eliminados correctamente" 
                : "Algunos directorios no pudieron ser eliminados"
        ]);
    }

    /**
     * Eliminar múltiples archivos de forma masiva
     */
    public function massDeleteFiles(Request $request)
    {
        $request->validate([
            'files' => 'required|string'
        ]);

        $disk = $this->getDisk();
        $files = json_decode($request->input('files'), true);
        $results = [];
        $allSuccess = true;
        $deletedCount = 0;

        foreach ($files as $file) {
            // Normalizar ruta
            $filePath = trim($file, '/');
            if (strpos($filePath, 'client_system/') !== 0) {
                $filePath = 'client_system/' . $filePath;
            }

            try {
                // Verificar si el archivo existe
                if (!$disk->exists($filePath)) {
                    $results[$file] = [
                        'success' => false,
                        'message' => 'El archivo no existe'
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Eliminar el archivo
                $deleted = $disk->delete($filePath);

                if ($deleted) {
                    $results[$file] = [
                        'success' => true,
                        'message' => 'Archivo eliminado correctamente'
                    ];
                    $deletedCount++;
                    \Log::info("Archivo eliminado: {$filePath}");
                } else {
                    throw new \Exception("Error al eliminar el archivo");
                }

            } catch (\Exception $e) {
                $results[$file] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error deleting file {$file}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'deleted_count' => $deletedCount,
            'total' => count($files),
            'results' => $results,
            'message' => $allSuccess 
                ? "Todos los archivos fueron eliminados correctamente" 
                : "Algunos archivos no pudieron ser eliminados"
        ]);
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

        $disk = $this->getDisk();
        $destinationInput = trim($request->path, '/');
        
        // Normalizar ruta de destino
        if (strpos($destinationInput, 'client_system/') !== 0) {
            $destinationInput = 'client_system/' . $destinationInput;
        }
        $destination = Str::finish($destinationInput, '/');
        
        $directories = json_decode($request->directories, true);
        $results = [];
        $allSuccess = true;

        foreach ($directories as $directory) {
            // Normalizar ruta de origen
            $sourceInput = trim($directory, '/');
            if (strpos($sourceInput, 'client_system/') !== 0) {
                $sourceInput = 'client_system/' . $sourceInput;
            }
            $source = Str::finish($sourceInput, '/');
            
            $dirname = basename(rtrim($source, '/'));
            $target = $destination . $dirname;

            try {
                // Verificaciones iniciales
                if (!$disk->exists($source)) {
                    throw new \Exception('El directorio origen no existe: ' . $source);
                }
                if ($disk->exists($target)) {
                    throw new \Exception('El directorio destino ya existe: ' . $target);
                }

                // Crear directorio principal
                if (!$disk->makeDirectory($target)) {
                    throw new \Exception('No se pudo crear el directorio destino: ' . $target);
                }

                // Obtener todos los contenidos (directorios y archivos)
                $allDirs = [];
                $allFiles = [];
                
                try {
                    $contents = $disk->listContents($source, true);
                    foreach ($contents as $item) {
                        if ($item->isDir()) {
                            $allDirs[] = $item->path();
                        } else {
                            $allFiles[] = $item->path();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("Error listing contents of {$source}: " . $e->getMessage());
                }

                // Crear subdirectorios primero
                foreach ($allDirs as $dir) {
                    $relativePath = Str::after($dir, $source);
                    $newDirPath = rtrim($target, '/') . '/' . ltrim($relativePath, '/');
                    
                    if (!$disk->directoryExists($newDirPath)) {
                        $disk->makeDirectory($newDirPath);
                    }
                }

                // Copiar archivos
                foreach ($allFiles as $file) {
                    $relativePath = Str::after($file, $source);
                    $newFilePath = rtrim($target, '/') . '/' . ltrim($relativePath, '/');
                    
                    // Asegurar que el directorio padre existe
                    $parentDir = dirname($newFilePath);
                    if (!$disk->exists($parentDir)) {
                        $disk->makeDirectory($parentDir);
                    }
                    
                    $disk->copy($file, $newFilePath);
                }

                $results[$directory] = [
                    'success' => true,
                    'message' => 'Directorio y subdirectorios copiados correctamente',
                    'new_path' => $target
                ];

            } catch (\Exception $e) {
                // Limpieza en caso de error
                if ($disk->exists($target)) {
                    try {
                        $disk->deleteDirectory($target);
                    } catch (\Exception $cleanupError) {
                        \Log::error("Error cleaning up {$target}: " . $cleanupError->getMessage());
                    }
                }

                $results[$directory] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error copiando {$directory}: " . $e->getMessage());
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

        $disk = $this->getDisk();
        $destinationInput = trim($request->path, '/');
        
        // Normalizar ruta de destino
        if (strpos($destinationInput, 'client_system/') !== 0) {
            $destinationInput = 'client_system/' . $destinationInput;
        }
        $destination = Str::finish($destinationInput, '/');
        
        $directories = json_decode($request->directories, true);
        $results = [];
        $allSuccess = true;

        foreach ($directories as $directory) {
            // Normalizar ruta de origen
            $sourceInput = trim($directory, '/');
            if (strpos($sourceInput, 'client_system/') !== 0) {
                $sourceInput = 'client_system/' . $sourceInput;
            }
            $source = rtrim($sourceInput, '/');
            
            $dirname = basename($source);
            $target = rtrim($destination, '/') . '/' . $dirname;

            try {
                // Verificar si el origen existe
                if (!$disk->exists($source)) {
                    $results[$directory] = [
                        'success' => false,
                        'message' => 'El directorio origen no existe: ' . $source
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Verificar si el destino ya existe
                if ($disk->exists($target)) {
                    $results[$directory] = [
                        'success' => false,
                        'message' => 'El directorio destino ya existe: ' . $target
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Verificar que no se esté moviendo a una subcarpeta de sí mismo
                if (strpos($target, $source . '/') === 0) {
                    $results[$directory] = [
                        'success' => false,
                        'message' => 'No se puede mover un directorio dentro de sí mismo'
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Mover el directorio
                $moved = $disk->move($source, $target);

                if ($moved) {
                    $results[$directory] = [
                        'success' => true,
                        'message' => 'Directorio movido correctamente',
                        'old_path' => $source,
                        'new_path' => $target
                    ];
                    
                    \Log::info("Directorio movido: {$source} -> {$target}");
                } else {
                    throw new \Exception("Error al mover el directorio");
                }

            } catch (\Exception $e) {
                $results[$directory] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error moving directory {$directory}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'results' => $results
        ]);
    }

    /**
     * Copiar archivos individuales a una nueva ubicación
     */
    public function copyFiles(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'file_paths' => 'required|json'
        ]);

        $disk = $this->getDisk();
        $destinationInput = trim($request->path, '/');
        
        // Normalizar ruta de destino
        if (strpos($destinationInput, 'client_system/') !== 0) {
            $destinationInput = 'client_system/' . $destinationInput;
        }
        $destination = Str::finish($destinationInput, '/');
        
        $files = json_decode($request->file_paths, true);
        $results = [];
        $allSuccess = true;

        foreach ($files as $file) {
            // Normalizar ruta de origen
            $sourceInput = trim($file, '/');
            if (strpos($sourceInput, 'client_system/') !== 0) {
                $sourceInput = 'client_system/' . $sourceInput;
            }
            $source = $sourceInput;
            
            $filename = basename($source);
            $target = rtrim($destination, '/') . '/' . $filename;

            try {
                // Verificaciones iniciales
                if (!$disk->exists($source)) {
                    throw new \Exception('El archivo origen no existe: ' . $source);
                }
                if ($disk->exists($target)) {
                    throw new \Exception('El archivo destino ya existe: ' . $target);
                }

                // Asegurar que el directorio de destino existe
                $targetDir = dirname($target);
                if (!$disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                // Copiar el archivo
                if (!$disk->copy($source, $target)) {
                    throw new \Exception('No se pudo copiar el archivo');
                }

                $results[$file] = [
                    'success' => true,
                    'message' => 'Archivo copiado correctamente',
                    'new_path' => $target
                ];

            } catch (\Exception $e) {
                $results[$file] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error copiando archivo {$file}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $allSuccess,
            'results' => $results
        ]);
    }

    /**
     * Mover archivos individuales a una nueva ubicación
     */
    public function moveFiles(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'file_paths' => 'required|json'
        ]);

        $disk = $this->getDisk();
        $destinationInput = trim($request->path, '/');
        
        // Normalizar ruta de destino
        if (strpos($destinationInput, 'client_system/') !== 0) {
            $destinationInput = 'client_system/' . $destinationInput;
        }
        $destination = Str::finish($destinationInput, '/');
        
        $files = json_decode($request->file_paths, true);
        $results = [];
        $allSuccess = true;

        foreach ($files as $file) {
            // Normalizar ruta de origen
            $sourceInput = trim($file, '/');
            if (strpos($sourceInput, 'client_system/') !== 0) {
                $sourceInput = 'client_system/' . $sourceInput;
            }
            $source = $sourceInput;
            
            $filename = basename($source);
            $target = rtrim($destination, '/') . '/' . $filename;

            try {
                // Verificar si el origen existe
                if (!$disk->exists($source)) {
                    $results[$file] = [
                        'success' => false,
                        'message' => 'El archivo origen no existe: ' . $source
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Verificar si el destino ya existe
                if ($disk->exists($target)) {
                    $results[$file] = [
                        'success' => false,
                        'message' => 'El archivo destino ya existe: ' . $target
                    ];
                    $allSuccess = false;
                    continue;
                }

                // Asegurar que el directorio de destino existe
                $targetDir = dirname($target);
                if (!$disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                // Mover el archivo
                $moved = $disk->move($source, $target);

                if ($moved) {
                    $results[$file] = [
                        'success' => true,
                        'message' => 'Archivo movido correctamente',
                        'old_path' => $source,
                        'new_path' => $target
                    ];
                    
                    \Log::info("Archivo movido: {$source} -> {$target}");
                } else {
                    throw new \Exception("Error al mover el archivo");
                }

            } catch (\Exception $e) {
                $results[$file] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $allSuccess = false;
                \Log::error("Error moving file {$file}: " . $e->getMessage());
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

    /**
     * Método público para listar directorios (usado por el clipboard AJAX)
     * Retorna solo los directorios de un path específico
     */
    public function listDirectories(Request $request)
    {
        try {
            $input = trim($request->input('path', ''), '/');
            
            // Normalizar la ruta - remover 'client_system/' si ya está presente
            if (strpos($input, 'client_system/') === 0) {
                $subpath = substr($input, strlen('client_system/'));
            } else {
                $subpath = $input;
            }

            $disk = $this->getDisk();
            $basePath = $subpath !== '' ? "client_system/{$subpath}" : 'client_system';

            // Verificar si el directorio existe
            if (!$disk->directoryExists($basePath)) {
                return response()->json([]);
            }

            $dirs = [];
            $contents = $disk->listContents($basePath, false);

            foreach ($contents as $item) {
                if ($item->isDir()) {
                    // Retornar la ruta relativa sin 'client_system/'
                    $relativePath = substr($item->path(), strlen('client_system/'));
                    $relativePath = ltrim($relativePath, '/');
                    
                    $dirs[] = [
                        'name' => basename($item->path()),
                        'path' => $relativePath
                    ];
                }
            }

            return response()->json($dirs);

        } catch (\Exception $e) {
            \Log::error("Error listing directories: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
                'folder_name' => 'required|string|max:1024',
                'parent_path' => 'nullable|string',
                'is_mip' => 'nullable|boolean'
            ]);

            $folderName = trim($request->input('folder_name'));
            $parentPath = $request->input('parent_path');
            $isMip = $request->input('is_mip', false);

            // Validar que el nombre no esté vacío después de trim
            if (empty($folderName)) {
                return back()->withErrors(['folder_name' => 'El nombre de la carpeta no puede estar vacío']);
            }

            // Determinar la ruta base según el tipo
            $basePath = $isMip ? $this->mip_path : $this->path;

            // Construir la ruta completa
            $fullPath = $parentPath
                ? rtrim($parentPath, '/') . '/' . $folderName
                : rtrim($basePath, '/') . '/' . $folderName;

            $disk = $this->getDisk();

            // Verificar si la carpeta ya existe (doble verificación por seguridad)
            if ($disk->directoryExists($fullPath)) {
                return back()->withErrors(['folder_name' => 'La carpeta "' . $folderName . '" ya existe en esta ubicación']);
            }

            // Crear la carpeta con manejo de errores mejorado
            try {
                $created = $disk->makeDirectory($fullPath);

                if (!$created) {
                    throw new \Exception('No se pudo crear la carpeta');
                }

                // Verificar que se creó correctamente
                if (!$disk->directoryExists($fullPath)) {
                    throw new \Exception('La carpeta no existe después de crearla');
                }

                // Si es una carpeta MIP, crear la estructura completa
                if ($isMip) {
                    $this->createMipStructure($fullPath);
                }

                return back()->with('success', 'Carpeta "' . $folderName . '" creada exitosamente');

            } catch (\Exception $e) {
                // Si falla, verificar si se creó parcialmente y limpiar
                if ($disk->directoryExists($fullPath)) {
                    try {
                        $disk->deleteDirectory($fullPath);
                    } catch (\Exception $cleanupError) {
                        Log::error('Error cleaning up failed directory creation: ' . $cleanupError->getMessage());
                    }
                }
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error creating directory: ' . $e->getMessage());
            return back()->withErrors(['folder_name' => 'Error al crear la carpeta: ' . $e->getMessage()])->withInput();
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
                'new_name' => 'required|string|max:1024|regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_\.]+$/',
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