<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerProperties;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerPropertiesSeeder extends Seeder
{
    protected $table_name = 'customer_properties';
    protected $table_customers = 'customer';
    protected $permissions = [1, 2, 3, 4, 5];
    
    public function run(): void
    {
        $rowCount = DB::table($this->table_name)->count();
        $countCustomers = DB::table($this->table_customers)->count();

        if($countCustomers > 0) {
            if ($rowCount <= 0) {
                $customers = Customer::all();
                foreach($customers as $customer) {
                    if($customer->general_sedes == 0) {
                        CustomerProperties::insert([
                            'customer_id' => $customer->id,
                            'property_id' => $this->permissions[0],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        for($i = 1; $i < count($this->permissions); $i++) {
                            CustomerProperties::insert([
                                'customer_id' => $customer->id,
                                'property_id' => $this->permissions[$i],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }            
            } 
        }
    }
}
