<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder {

	public function run(): void {
		$permissions = [
			[
				'name' => 'write_branch', // 1
				'type' => 'w',
			],
			[
				'name' => 'write_calendary', // 2
				'type' => 'w',
			],
			[
				'name' => 'write_customer', // 3
				'type' => 'w',
			],
			[
				'name' => 'write_lot', // 4
				'type' => 'w'
			],
			[
				'name' => 'write_order', // 5
				'type' => 'w',
			],
			[ 
				'name' => 'write_pest', // 6
				'type' => 'w',
			],
			[
				'name' => 'write_product', // 7
				'type' => 'w',
			],
			[
				'name' => 'write_point', // 8
				'type' => 'w',
			],
			[
				'name' => 'write_service', // 9
				'type' => 'w',
			],
			[
				'name' => 'write_user', // 10
				'type' => 'w',
			],
			[
				'name' => 'write_warehouse', // 11
				'type' => 'w'
			],
			[
				'name' => 'write_system_client', // 12
				'type' => 'w'
			],
			[
				'name' => 'read_system_client', // 13
				'type' => 'w'
			],
		];

		$data = array_map(function ($permission) {
			return [
				'name' => $permission['name'],
				'type' => $permission['type'],
				'guard_name' => 'web',
				'created_at' => now(),
				'updated_at' => null,
			];
		}, $permissions);

		Permission::insert($data);

	}

}
