<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'min' => 0],
            ['code' => 'SAVE25', 'type' => 'fixed_amount', 'value' => 25, 'min' => 100],
            ['code' => 'FIRSTVISIT', 'type' => 'percentage', 'value' => 15, 'min' => 50],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                [
                    'description' => "{$coupon['code']} promotional discount",
                    'discount_type' => $coupon['type'],
                    'discount_value' => $coupon['value'],
                    'max_uses' => 100,
                    'current_uses' => 0,
                    'min_booking_amount' => $coupon['min'],
                    'valid_from' => now()->subDays(7)->toDateString(),
                    'valid_until' => now()->addMonths(3)->toDateString(),
                    'is_active' => true,
                ]
            );
        }
    }
}
