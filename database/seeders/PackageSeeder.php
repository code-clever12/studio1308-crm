<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Bridal Package',
                'description' => 'Hair, makeup, and a manicure for your big day.',
                'services' => ["Women's Haircut & Style", 'Bridal Makeup', 'Gel Manicure'],
                'discount' => 15,
            ],
            [
                'name' => 'Spa Day Package',
                'description' => 'Unwind with a massage, facial, and pedicure.',
                'services' => ['Swedish Massage (60 min)', 'Signature Facial', 'Spa Pedicure'],
                'discount' => 10,
            ],
            [
                'name' => 'Mani-Pedi Combo',
                'description' => 'Classic manicure paired with a spa pedicure.',
                'services' => ['Classic Manicure', 'Spa Pedicure'],
                'discount' => 10,
            ],
        ];

        foreach ($packages as $package) {
            $services = Service::whereIn('name', $package['services'])->get();

            if ($services->isEmpty()) {
                continue;
            }

            $fullPrice = $services->sum('price');
            $price = round($fullPrice * (1 - $package['discount'] / 100), 2);

            Package::firstOrCreate(
                ['name' => $package['name']],
                [
                    'description' => $package['description'],
                    'price' => $price,
                    'services_json' => $services->pluck('id')->all(),
                    'total_duration_minutes' => $services->sum('duration_minutes'),
                    'discount_percentage' => $package['discount'],
                    'is_active' => true,
                ]
            );
        }
    }
}
