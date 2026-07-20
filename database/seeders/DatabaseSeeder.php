<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SalonSeeder::class,
            ServiceCatalogSeeder::class,
            StaffSeeder::class,
            CustomerSeeder::class,
            AppointmentSeeder::class,
            PayoutSeeder::class,
            ProductSeeder::class,
            PackageSeeder::class,
            CouponSeeder::class,
        ]);
    }
}
