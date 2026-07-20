<?php

namespace Database\Factories;

use App\Models\ConsentForm;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsentForm>
 */
class ConsentFormFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => fake()->randomElement(['General Consent', 'Chemical Treatment Waiver', 'Allergy Disclosure']),
            'description' => fake()->sentence(),
            'fields_json' => [
                ['id' => 'allergies', 'label' => 'Do you have any known allergies?', 'type' => 'text', 'required' => true],
                ['id' => 'agree', 'label' => 'I consent to the treatment described above', 'type' => 'checkbox', 'required' => true],
            ],
            'is_active' => true,
        ];
    }
}
