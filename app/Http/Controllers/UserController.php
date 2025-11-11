<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\SimpleExcel\SimpleExcelWriter;

use App\Models\Administrative;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ContractType;
use App\Models\SimpleRole;
use App\Models\Status;
use App\Models\Technician;
use App\Models\User;
use App\Models\UserContract;
use App\Models\UserFile;
use App\Models\WorkDepartment;
use App\Models\Filenames;
use App\Models\UserCustomer;
use App\Models\DirectoryPermission;
use App\Models\OrderTechnician;

class UserController extends Controller
{
	private $files_path = 'users/files/';
	private static $states_route = 'datas/json/Mexico_states.json';
	private static $cities_route = 'datas/json/Mexico_cities.json';
	private $path = 'client_system/';
	private $user_path = 'users/';

	private $size = 25;

	public static function verifyData($user, $hasContract)
	{
		$user_data = $user->role_id == 3 ? Technician::where('user_id', $user->id)->first() : Administrative::where('user_id', $user->id)->first();

		if (
			$user_data->curp != null && $user_data->rfc != null && $user_data->nss != null &&
			$user_data->address != null && $user_data->colony != null && $user_data->city != null &&
			$user_data->state != null && $user_data->country != null && $user_data->zip_code != null &&
			$user_data->birthdate != null && $user_data->hiredate != null && $user_data->salary != null &&
			$hasContract != false
		) {
			$user->status_id = 2;
			$user->save();
			return true;
		} else {
			return false;
		}
	}

	private function listDirectoriesRecursively($path)
	{
		$directories = [];

		if (Storage::disk('google')->exists($path)) {
			$subdirectories = Storage::disk('google')->directories($path);
			foreach ($subdirectories as $directory) {
				$directories[] = [
					'name' => basename($directory),
					'path' => $directory . '/',
					'directories' => $this->listDirectoriesRecursively($directory)
				];
			}
		}

		return $directories;
	}

	public function index(): View
	{
		$users = User::whereNot('role_id', 4)->orderBy('name', 'DESC')->paginate($this->size);
		$roles = SimpleRole::where('id', '!=', 4)->get();
		$wk_depts = WorkDepartment::where('id', '!=', 1)->get();
		$types = ['Usuario Interno', 'Cliente'];

		$navigation = [
			'Usuarios' => route('user.index'),
		];

		return view(
			'user.index',
			compact(
				'users',
				'roles',
				'wk_depts',
				'navigation'
			)
		);
	}

	public function create(): View
	{
		$disk = Storage::disk('public');
		$local_dirs = $disk->directories($this->path);
		sort($local_dirs);
		$statuses = Status::all();
		$work_departments = WorkDepartment::where('id', '!=', 1)->get();
		$roles = SimpleRole::whereNotIn('id', [4, 5])->get();
		$branches = Branch::all();
		$companies = Company::all();

		$navigation = [
			'Usuario interno' => route('user.create'),
			'Cliente' => route('user.create.client')
		];

		return view(
			'user.create.intern',
			compact(
				'local_dirs',
				'statuses',
				'work_departments',
				'roles',
				'branches',
				'companies',
				'navigation'
			)
		);
	}

	public function createClient(): View
	{
		$disk = Storage::disk('google');
		$local_dirs = $disk->directories($this->path);
		sort($local_dirs);

		$navigation = [
			'Usuario interno' => route('user.create'),
			'Cliente' => route('user.create.client')
		];

		return view(
			'user.create.client',
			compact(
				'local_dirs',
				'navigation'
			)
		);
	}

	public function search(Request $request)
	{
		$size = $request->input('size');
		$direction = $request->input('direction', 'DESC');
		$query_users = User::where('role_id', '!=', 4);
		$wk_depts = WorkDepartment::where('id', '!=', 1)->get();

		if ($request->name) {
			$query_users = $query_users->where('name', 'LIKE', '%' . $request->name . '%');
		}

		if ($request->username) {
			$query_users = $query_users->where('username', 'LIKE', '%' . $request->username . '%');
		}

		if ($request->email) {
			$query_users = $query_users->where('email', 'LIKE', '%' . $request->email . '%');
		}

		if ($request->role) {
			$query_users = $query_users->where('role_id', $request->role);
		}

		if ($request->wk_dept) {
			$query_users = $query_users->where('work_department_id', $request->wk_dept);
		}

		$users = $query_users->orderBy('name', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());
		$roles = SimpleRole::where('id', '!=', 4)->get();

		return view(
			'user.index',
			compact(
				'users',
				'roles',
				'wk_depts',
			)
		);
	}

	public function store(Request $request): RedirectResponse
	{
		$type = 1;
		$files = Filenames::where('type', 'user')->get();

		// Crear nuevo usuario
		$user = new User($request->all());
		$user->password = bcrypt($request->password);
		$user->nickname = $request->password;
		$user->status_id = 2;
		$user->type_id = $type;
		$user->save();

		// Definir el permiso para el usuario
		$role = Role::where('simple_role_id', $user->role_id)->where('work_id', $user->work_department_id)->first();
		if ($role) {
			$user->assignRole($role->name);
		}

		// Archivos del usuario
		foreach ($files as $file) {
			UserFile::insert([
				'user_id' => $user->id,
				'filename_id' => $file->id,
			]);
		}

		// Asignar a la tabla correspondiente
		if ($user->role_id == 3) {
			$technician = new Technician($request->all());
			$technician->company_id = 1;
			$technician->user_id = $user->id;
			$technician->contract_type_id = 1;
			$technician->save();
		} else {
			$admin = new Administrative($request->all());
			$admin->user_id = $user->id;
			$admin->contract_type_id = 1;
			$admin->company_id = 1;
			$admin->save();

			if ($request->role_id == 2 && $request->work_department_id == 8) {
				$technician = new Technician($request->all());
				$technician->user_id = $user->id;
				$technician->contract_type_id = 1;
				$technician->save();
			}
		}

		// Crear contrato del usuario
		$user_contract = new UserContract();
		$user_contract->user_id = $user->id;
		$user_contract->contract_type_id = 1;
		$user_contract->save();

		return redirect()->route('user.index', ['type' => $type, 'page' => 1]);
	}

	public function storeClient(Request $request): RedirectResponse
	{
		$directories = json_decode($request->input('directories'));
		$selected_sedes = json_decode($request->input('sedes'));
		$type = 2;

		$files = Filenames::where('type', 'user')->get();

		// Crear nuevo usuario
		$user = new User($request->all());
		$user->password = bcrypt($request->password);
		$user->nickname = $request->password;
		$user->status_id = 2;
		$user->type_id = $type;
		$user->role_id = 5;
		$user->save();


		// Definir el rol para el usuario sin departamento de trabajo
		$role = Role::where('simple_role_id', $user->role_id)->where('work_id', null)->first();
		if ($role) {
			$user->assignRole($role->name);
		}

		if (!empty($directories)) {
			foreach ($directories as $dir) {
				DirectoryPermission::insert([
					'path' => $dir,
					'user_id' => $user->id,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}

		if (!empty($selected_sedes)) {
			foreach ($selected_sedes as $customerId) {
				UserCustomer::insert([
					'user_id' => $user->id,
					'customer_id' => $customerId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}

		}

		return redirect()->route('user.index', ['type' => $type, 'page' => 1]);
	}

	public function show(string $id, string $type): View
	{
		$user = User::find($id);
		$status = Status::find($user->status_id);
		$work_departments = WorkDepartment::all();
		$roles = SimpleRole::all();
		$branches = Branch::all();
		$companies = Company::all();
		$contracts = ContractType::all();
		$user_contracts = UserContract::where('user_id', $id)->get();

		$states = json_decode(file_get_contents(public_path(UserController::$states_route)), true);
		$cities = json_decode(file_get_contents(public_path(UserController::$cities_route)), true);

		return view(
			'user.show',
			compact(
				'user',
				'status',
				'work_departments',
				'roles',
				'branches',
				'companies',
				'contracts',
				'user_contracts',
				'states',
				'type',
				'cities',
			)
		);
	}

	public function edit(string $id)
	{
		$dates = [];
		$user = User::find($id);
		$filenames = Filenames::where('type', 'user')->orderBy('name')->get();
		$statuses = Status::all();
		$work_departments = $user->role_id != 3 ? WorkDepartment::where('id', '!=', 1)->get() : WorkDepartment::where('id', 8)->get();
		$roles = $user->role_id != 3 ? SimpleRole::whereNotIn('id', [3, 4, 5])->get() : SimpleRole::where('id', 3)->get();
		$branches = Branch::all();
		$companies = Company::all();
		$contracts = ContractType::all();
		$contract = $user->contracts()->latest()->first();

		$states = json_decode(file_get_contents(public_path(UserController::$states_route)), true);
		$cities = json_decode(file_get_contents(public_path(UserController::$cities_route)), true);

		$dates = $contract ? array(
			'startdate' => $contract->contract_startdate,
			'enddate' => $contract->contract_enddate,
		) : [];

		return view(
			'user.edit.intern',
			compact(
				'user',
				'statuses',
				'work_departments',
				'roles',
				'branches',
				'companies',
				'contracts',
				'dates',
				'states',
				'cities',
				'filenames',
			)
		);
	}

	public function editClient(string $id)
	{
		$clients_data = [];
		$user = User::find($id);
		$contract = $user->contracts()->latest()->first();
		$clients = Customer::whereIn('general_sedes', Customer::whereIn('id', $user->customers()->get()->pluck('id'))->get()->pluck('general_sedes'))->get();

		$clients = $user->customers()->get();
		foreach ($clients as $client) {
			$clients_data[] = [
				'id' => $client->id,
				'name' => $client->name,

				'is_checked' => true,
			];
		}


		if ($user->type_id == 1) {
			$dates = array(
				'startdate' => $contract->contract_startdate,
				'enddate' => $contract->contract_enddate,
			);
		}

		$disk = Storage::disk('google');
		$local_dirs = $disk->directories($this->path);
		sort($local_dirs);

		$clients = $clients_data;

		return view(
			'user.edit.client',
			compact(
				'user',
				'clients',
				'local_dirs',
				'clients',
			)
		);
	}

	public function update(Request $request, string $id)
	{
		$user_data = [];
		$hasContract = false;
		$changes = '';
		$directories = json_decode($request->input('directories'));
		$selected_sedes = json_decode($request->input('sedes'));
		$user = User::findOrFail($id);
		$user->fill($request->all());

		if ($request->password) {
			$user->nickname = $request->input('password');
			$user->password = Hash::make($request->input('password'));
		}

		$user->save();

		if ($user->role_id != 5) {
			if ($user->role_id == 3) {
				$user_data = Technician::where('user_id', $id)->first();
			} else {
				$user_data = Administrative::where('user_id', $id)->first();
			}

			if ($user_data) {
				$user_data->fill($request->all());
				$user_data->save();
			}

			if ($request->role_id == 2 && $request->work_department_id == 8) {
				$technician = Technician::where('user_id', $user->id)->first();
				if (!$technician) {
					$technician = new Technician($request->all());
					$technician->user_id = $user->id;
					$technician->contract_type_id = 1;
					$technician->save();
				}
			}

			if ($request->contract) {
				UserContract::updateOrCreate(
					[
						'user_id' => $id,
						'contract_type_id' => $request->contract,
					],
					[
						'contract_startdate' => $request->contract_startdate,
						'contract_enddate' => $request->contract_enddate,
					]
				);

				if (isset($request->contract_startdate)) {
					$hasContract = true;
				}
			}

			$role = Role::where('simple_role_id', $user->role_id)->where('work_id', $user->work_department_id)->first();
			if ($role) {
				$user->syncRoles([$role->name]);
			}

			if ($user->status_id != 3 && $user->role_id != 5) {
				UserController::verifyData($user, $hasContract);
			}
		} else {
			if (!empty($directories)) {

				$dir_perms = DirectoryPermission::where('user_id', $user->id)->get();
				$new_paths = array_diff($directories, $dir_perms->pluck('path')->toArray());
				$delete_paths = array_diff($dir_perms->pluck('path')->toArray(), $directories);

				foreach ($delete_paths as $path) {
					DirectoryPermission::where('path', $path)->delete();
				}

				foreach ($new_paths as $path) {
					DirectoryPermission::insert([
						'path' => $path,
						'user_id' => $user->id,
						'created_at' => now(),
						'updated_at' => now()
					]);
				}
			}

			if (!empty($selected_sedes)) {
				$customers = $user->customers()->get();
				$new_customers = array_diff($selected_sedes, $customers->pluck('id')->toArray());
				$delete_customers = array_diff($customers->pluck('id')->toArray(), $selected_sedes);

				foreach ($delete_customers as $customerId) {
					UserCustomer::where('user_id', $user->id)->where('customer_id', $customerId)->delete();
				}

				foreach ($new_customers as $customer_id) {
					UserCustomer::insert([
						'user_id' => $user->id,
						'customer_id' => $customer_id,
						'created_at' => now(),
						'updated_at' => now()
					]);
				}
			}

			// Definir permisos para el usuario
			$role = Role::where('simple_role_id', $user->role_id)->where('work_id', $user->work_department_id)->first();
			if ($role) {
				$user->syncRoles([$role->name]);
			}
		}

		return back();
	}

	public function uploadFile(Request $request, string $userId)
	{
		$request->validate([
			'file' => 'required|mimes:jpeg,png,jpg,pdf|max:5120'
		]);

		$file = $request->file('file');

		$disk = Storage::disk('public');
		$user = User::find($userId);

		$user_file = UserFile::updateOrCreate([
			'user_id' => $user->id,
			'filename_id' => $request->filename_id
		], [
			'expirated_at' => $request->expirated_at,
			'updated_at' => now()
		]);

		if ($user_file->path && $disk->exists($user_file->path)) {
			$disk->delete($user_file->path);
		}

		$fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$extension = $file->getClientOriginalExtension();
		$newFileName = $fileName . '_' . time() . '.' . $extension;

		$filePath = $this->files_path . $newFileName;

		$disk->put($filePath, file_get_contents($file));

		$user_file->update(['path' => $filePath]);

		return back();
	}

	public function uploadFileByName(Request $request, string $userId)
	{
		$request->validate([
			'file'         => 'required|mimes:jpeg,png,jpg,pdf|max:5120',
			'filename'     => 'required|string|max:255',
			'expirated_at' => 'nullable|date',
		]);

		$user = User::findOrFail($userId);
		$disk = Storage::disk('public');

		$file = $request->file('file');
		$fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$extension = $file->getClientOriginalExtension();
		$newFileName = $fileName . '_' . time() . '.' . $extension;

		$filePath = $this->files_path . $newFileName;

		$disk->put($filePath, file_get_contents($file));

		

		UserFile::create([
			'user_id'     => $user->id,
			'file_name'   => $request->input('filename'),
			'path'        => $filePath,
			'expirated_at'=> $request->input('expirated_at'),
		]);

    return back();
	}

	public function downloadFile(string $id)
	{
		try {
			$userfile = UserFile::find($id);

			if (!$userfile) {
				abort(404);
			}

			if (Storage::disk('public')->exists($userfile->path)) {
				return Storage::disk('public')->download($userfile->path);
			}
			return response()->json(['error' => 'File not found.'], 404);
		} catch (\Exception $e) {
			return response()->json(['error' => 'An error occurred while downloading the file.'], 500);
		}
	}

	public function destroyFile(string $fileId)
	{
		$disk = Storage::disk('public');

		$userFile = UserFile::find($fileId);

		if ($userFile->path && $disk->exists($userFile->path)) {
			$disk->delete($userFile->path);
		}

		$userFile->delete();

		return back()->with('success', 'File deleted successfully');
	}

	public function export(Request $request)
	{
		$exportData = [];
		switch ($request->input('option_export')) {
			case 1:
				$users = User::all();
				foreach ($users as $user) {
					$data = [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'role' => optional($user->role)->name,
						'status' => optional($user->status)->name,
						'company' => optional($user->roleData->company)->name,
						'branch' => optional($user->roleData->branch)->name,
						'contract' => optional($user->roleData->contractType)->name,
						'curp' => $user->roleData->curp,
						'rfc' => $user->roleData->rfc,
						'nss' => $user->roleData->nss,
						'phone' => $user->roleData->phone,
						'company_phone' => $user->roleData->company_phone,
						'address' => $user->roleData->address,
						'colony' => $user->roleData->colony,
						'city' => $user->roleData->city,
						'state' => $user->roleData->state,
						'country' => $user->roleData->country,
						'zip_code' => $user->roleData->zip_code,
						'birthdate' => $user->roleData->birthdate,
						'hiredate' => $user->roleData->hiredate,
						'salary' => $user->roleData->salary,
						'clabe' => $user->roleData->clabe,
						'signature' => $user->roleData->signature,
						'created_at' => $user->roleData->created_at,
					];
					$exportData[] = $data;
				}
				$columnNames = array_keys($data);
				break;

			case 2:
				$columnNames = Schema::getColumnListing('user_file');
				$exportData = UserFile::all()->toArray();
				break;
		}

		SimpleExcelWriter::streamDownload('your-export.xlsx')
			->addHeader($columnNames)
			->addRows($exportData)
			->toBrowser();

		return redirect()->back();
	}

	public function directories(Request $request)
	{
		try {
			$path = $request->input('path');
			$disk = Storage::disk('google');
			$dirs = $disk->directories($path);
			sort($dirs);

			$data = [
				'directories' => $dirs
			];

			return response()->json($data);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function searchSedes(Request $request)
	{
		$sedes_data = [];
		$searchTerm = $request->search;

		$customers = Customer::where('name', 'LIKE', "%{$searchTerm}%")
			->where(function ($query) {
				$query->where('service_type_id', 1)
					->orWhere('general_sedes', '!=', 0);
			})
			->get();

		foreach ($customers as $customer) {
			$sedes_data[] = [
				'id' => $customer->id,
				'name' => $customer->name,
				'matrix' => [
					'id' => $customer->matrix->id ?? null,
					'name' => $customer->matrix->name ?? '-'
				],
				'is_checked' => false,
			];
		}

		$data = [
			'sedes' => $sedes_data,
		];
		return response()->json($data);
	}

	public function destroy(string $id): RedirectResponse
	{
		$user = User::findOrFail($id);

		if (!$user) {
			return redirect()->route('user.index')->with('error', 'User not found.');
		}

		$user->syncRoles([]);

		UserFile::where('user_id', $user->id)->delete();
		UserContract::where('user_id', $user->id)->delete();
		UserCustomer::where('user_id', $user->id)->delete();

		if ($user->role_id == 3) {
			$techn = Technician::where('user_id', $user->id)->first();
			if ($techn) {
				OrderTechnician::where('technician_id', $techn->id)->delete();
				$techn->delete();
			}
		} else {
			Administrative::where('user_id', $user->id)->delete();
			if ($user->role_id == 2 && $user->work_department_id == 8) {
				$technician = Technician::where('user_id', $user->id)->first();
				if ($technician) {
					OrderTechnician::where('technician_id', $technician->id)->delete();
					$technician->delete();
				}
			}
		}

		$user->delete();

		return redirect()->route('user.index');
	}
}
