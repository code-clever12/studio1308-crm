<?php

namespace Database\Seeders;

use App\Models\ACHBankAccount;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = [
            ['name' => 'Jordan Lee', 'email' => 'jordan@ritualsalon.test', 'commission' => 25],
            ['name' => 'Morgan Blake', 'email' => 'morgan@ritualsalon.test', 'commission' => 20],
            ['name' => 'Casey Nguyen', 'email' => 'casey@ritualsalon.test', 'commission' => 22],
            ['name' => 'Riley Santos', 'email' => 'riley@ritualsalon.test', 'commission' => 20],
            ['name' => 'Sam Cooper', 'email' => 'sam@ritualsalon.test', 'commission' => 30],
        ];

        $allServiceIds = Service::pluck('id')->all();

        foreach ($staffMembers as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => '212555'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'password' => bcrypt('password'),
                    'role' => 'staff',
                    'timezone' => 'America/New_York',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $staff = Staff::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => "{$data['name']} is a licensed professional with years of experience.",
                    'commission_rate' => $data['commission'],
                    'status' => 'active',
                    'hire_date' => now()->subYears(rand(1, 4))->subDays(rand(0, 300)),
                ]
            );

            // Working Mon-Sat (1-6), Sunday (0) off.
            for ($day = 0; $day <= 6; $day++) {
                StaffSchedule::firstOrCreate(
                    ['staff_id' => $staff->id, 'day_of_week' => $day],
                    [
                        'start_time' => $day === 0 ? null : '09:00:00',
                        'end_time' => $day === 0 ? null : '18:00:00',
                        'break_start' => $day === 0 ? null : '13:00:00',
                        'break_end' => $day === 0 ? null : '13:30:00',
                        'is_working_day' => $day !== 0,
                    ]
                );
            }

            // Assign a random subset of services to this staff member.
            $assigned = collect($allServiceIds)->shuffle()->take(min(6, count($allServiceIds)));
            $staff->services()->syncWithoutDetaching($assigned->mapWithKeys(fn ($id) => [$id => ['is_available' => true]]));

            // First two staff have verified ACH accounts on file for payouts.
            if ($index < 2) {
                $accountNumber = fake()->numerify('##########');

                ACHBankAccount::firstOrCreate(
                    ['staff_id' => $staff->id],
                    [
                        'bank_account_routing_number' => fake()->numerify('#########'),
                        'bank_account_number' => $accountNumber,
                        'bank_account_holder_name' => $data['name'],
                        'bank_name' => fake()->randomElement(['Chase', 'Bank of America', 'Wells Fargo']),
                        'verification_status' => 'verified',
                        'last_4_digits' => substr($accountNumber, -4),
                    ]
                );
            }
        }
    }
}
