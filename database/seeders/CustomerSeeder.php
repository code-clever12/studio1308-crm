<?php

namespace Database\Seeders;

use App\Models\LoyaltyPoints;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'customer@ritualsalon.test'],
            [
                'name' => 'Taylor Kim',
                'phone' => '2125559999',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'timezone' => 'America/New_York',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $customers = User::factory()->count(14)->create();

        User::where('role', 'customer')->get()->each(function (User $customer) {
            $earned = fake()->numberBetween(0, 500);
            $redeemed = fake()->numberBetween(0, $earned);

            LoyaltyPoints::firstOrCreate(
                ['customer_id' => $customer->id],
                [
                    'balance' => $earned - $redeemed,
                    'earned_total' => $earned,
                    'redeemed_total' => $redeemed,
                    'last_activity_at' => now(),
                ]
            );
        });
    }
}
