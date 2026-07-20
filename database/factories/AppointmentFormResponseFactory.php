<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentFormResponse;
use App\Models\ConsentForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentFormResponse>
 */
class AppointmentFormResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'consent_form_id' => ConsentForm::factory(),
            'form_data_json' => [
                'allergies' => fake()->boolean(20) ? fake()->sentence() : 'None',
                'agree' => true,
            ],
        ];
    }
}
