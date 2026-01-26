<?php

namespace Database\Seeders;

use App\Models\ModelHasRoles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ModelHasRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role_id', '!=', 3)->get();
        $model_has_roles = ModelHasRoles::whereIn('model_id', $users->pluck('id'))->get();

        /*if (count($users) != count($model_has_roles)) {
            foreach ($users as $user) {
                $modelRole = ModelHasRoles::where('model_id', $user->id)->first();
                if (!$modelRole) {
                    $role = Role::where('simple_role_id', $user->role_id)->where('work_id', $user->work_department_id)->first();
                    if ($role) {
                        ModelHasRoles::insert([
                            'model_id' => $user->id,
                            'role_id' => $role->id,
                            'model_type' => 'App\Models\User'
                        ]);
                    }
                }
            }
        }*/

        foreach ($users as $user) {
            $modelRole = ModelHasRoles::where('model_id', $user->id)->first();

            if (!$modelRole) {
                $role = Role::where('simple_role_id', $user->role_id)
                    ->where('work_id', $user->work_department_id)
                    ->first();

                if ($role) {
                    // Asignar el rol al usuario
                    $user->assignRole($role->name);

                    // Obtener todos los permisos del rol y asignarlos al usuario
                    $permissions = $role->permissions;
                    foreach ($permissions as $permission) {
                        $user->givePermissionTo($permission->name);
                    }

                    echo "✅ Usuario {$user->id} -> Rol {$role->name} con " . count($permissions) . " permisos\n";
                } else {
                    echo "⚠️  Error: No se encontró rol para usuario {$user->id}\n";
                }
            } else {
                // Si ya tiene rol, solo asignar los permisos del rol
                $role = Role::find($modelRole->role_id);
                if ($role) {
                    $permissions = $role->permissions;
                    foreach ($permissions as $permission) {
                        $user->givePermissionTo($permission->name);
                    }
                    echo "✅ Usuario {$user->id} ya tenía rol, asignados " . count($permissions) . " permisos\n";
                }
            }
        }
    }
}
