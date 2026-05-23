<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ZoneType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	public function run(): void {
		$this->call([
			ZoneTypeSeeder::class,
			ExecFrequencySeeder::class,
			DaysWeekSeeder::class,
			ServicePrefixSeeder::class,
			StatusSeeder::class,
			ServiceStatusSeeder::class,
			OrderStatusSeeder::class,
			WorkDepartmentSeeder::class,
			PermissionSeeder::class,
			BranchSeeder::class,
			CompanySeeder::class,
			ContractTypeSeeder::class,
			RoleSeeder::class,
			PestCategorySeeder::class,
			ApplicationMethodSeeder::class,
			ServiceTypeSeeder::class,
			CompanyCategorySeeder::class,
			ReferenceTypeSeeder::class,
			LineBusinessSeeder::class,
			PurposeSeeder::class,
			BiocideSeeder::class,
			CfdiUsageSeeder::class,
			ToxicityCategoriesSeeder::class,
			presentationSeeder::class,
			QuestionOptionSeeder::class,
			TaxRegimeSeeder::class,
			UserTypeSeeder::class,
			UserSeeder::class,
			//AdministrativeSeeder::class,
			PropertiesSeeder::class,
			DefaultPropertiesSeeder::class,
			FilenamesSeeder::class,
			MovementTypeSeeder::class,
			// RecommendationsSeeder::class,
			MIPDirectorySeeder::class,
			MetricSeeder::class,
			SupplierCategorySeeder::class,
			SupplierSeeder::class,
			//PurchaseCustomerSeeder::class,
			WarehouseSeeder::class,
		]);
		//Branch::factory(5)->create();
		//User::factory(5)->create();
		//PestCategory::factory(5)->create();
		//PaymentMethod::factory(5)->create();
		//UserFile::factory(5)->create();
		//CustomerReference::factory(5)->create();
		//FrequencyType::factory(5)->create();
		//ApplicationMethod::factory(5)->create();
		//CompanyCategory::factory(5)->create();
		//Technician::factory(5)->create();
		//Administrative::factory(5)->create();
		//Branch::factory(5)->create();
		//CustomerContract::factory(5)->create();
		//CustomerReference::factory(5)->create();
	}
}
