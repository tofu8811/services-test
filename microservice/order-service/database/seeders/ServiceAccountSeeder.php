<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'API Gateway',
                'service_id' => env('API_GATEWAY_SERVICE_ID'),
                'service_secret' => env('API_GATEWAY_SERVICE_SECRET'),
            ],
            [
                'name' => 'Product Service',
                'service_id' => env('PRODUCT_SERVICE_ID'),
                'service_secret' => env('PRODUCT_SERVICE_SECRET'),
            ],
            [
                'name' => 'Order Service',
                'service_id' => env('ORDER_SERVICE_ID'),
                'service_secret' => env('ORDER_SERVICE_SECRET'),
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('service_accounts')->updateOrInsert(
                ['name' => $account['name']],
                $account
            );
        }
    }
}
