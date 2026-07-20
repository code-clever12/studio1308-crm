<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ConsentForm;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();

        $consentForm = ConsentForm::firstOrCreate(
            ['salon_id' => $salon->id, 'name' => 'Chemical Treatment Waiver'],
            [
                'description' => 'Required for any chemical hair treatment (color, perm, relaxer).',
                'fields_json' => [
                    ['id' => 'allergies', 'label' => 'Do you have any known allergies to hair chemicals?', 'type' => 'text', 'required' => true],
                    ['id' => 'prior_reaction', 'label' => 'Have you had a reaction to a chemical treatment before?', 'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No']],
                    ['id' => 'agree', 'label' => 'I understand the risks and consent to the treatment', 'type' => 'checkbox', 'required' => true],
                ],
                'is_active' => true,
            ]
        );

        $catalog = [
            'Hair' => [
                ["Women's Haircut & Style", 65, 45, false],
                ["Men's Haircut", 35, 30, false],
                ['Color & Highlights', 150, 120, true],
                ['Deep Conditioning Treatment', 45, 30, false],
                ['Blowout', 40, 30, false],
            ],
            'Nails' => [
                ['Classic Manicure', 35, 30, false],
                ['Gel Manicure', 50, 45, false],
                ['Spa Pedicure', 60, 45, false],
            ],
            'Skincare' => [
                ['Signature Facial', 90, 60, false],
                ['Deep Cleanse Facial', 110, 75, false],
            ],
            'Massage' => [
                ['Swedish Massage (60 min)', 95, 60, false],
                ['Deep Tissue Massage (90 min)', 140, 90, false],
            ],
            'Waxing' => [
                ['Eyebrow Waxing', 20, 15, false],
                ['Full Leg Waxing', 70, 45, false],
            ],
            'Makeup' => [
                ['Bridal Makeup', 150, 90, false],
                ['Special Occasion Makeup', 85, 60, false],
            ],
        ];

        foreach ($catalog as $categoryName => $services) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => "{$categoryName} services",
                    'display_order' => 0,
                ]
            );

            foreach ($services as [$name, $price, $duration, $requiresConsent]) {
                Service::firstOrCreate(
                    ['name' => $name],
                    [
                        'category_id' => $category->id,
                        'consent_form_id' => $requiresConsent ? $consentForm->id : null,
                        'description' => "{$name} performed by our licensed professionals.",
                        'price' => $price,
                        'duration_minutes' => $duration,
                        'buffer_time_minutes' => 15,
                        'requires_consent_form' => $requiresConsent,
                        'is_taxable' => true,
                        'is_active' => true,
                        'display_order' => 0,
                    ]
                );
            }
        }
    }
}
