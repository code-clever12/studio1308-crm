<?php

namespace Database\Seeders;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalonSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'admin@ritualsalon.test'],
            [
                'name' => 'Alex Rivera',
                'phone' => '2125551000',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'timezone' => 'America/New_York',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        Salon::firstOrCreate(
            ['owner_id' => $owner->id],
            [
                'name' => '1308Studio',
                'description' => 'A modern salon offering premium hair, skin, and wellness services.',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'phone' => '2125551234',
                'email' => 'hello@studio1308.com',
                'website' => 'https://studio1308.com',
                'timezone' => 'America/New_York',
                'opens_at' => '09:00:00',
                'closes_at' => '19:00:00',
                'cancellation_policy' => 'Free cancellation up to 24 hours before your appointment. Cancellations within 24 hours incur a $25 fee.',
                'deposit_percentage' => 25,
                'no_show_fee' => 25,
                'enable_tips' => true,
                'sales_tax_rate' => 8.875,
                'is_active' => true,
            ]
        );
    }
}
