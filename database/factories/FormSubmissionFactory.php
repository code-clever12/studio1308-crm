<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'payload' => [
                'full_name' => fake()->name(),
                'phone_number' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'hair_type' => fake()->randomElement(['straight', 'wavy', 'curly', 'coily']),
            ],
            'url' => fake()->url(),
            'utm_parameters' => [
                'utm_source' => fake()->randomElement(['facebook', 'google', 'instagram']),
                'utm_medium' => fake()->randomElement(['cpc', 'organic', 'referral']),
                'utm_campaign' => fake()->word(),
            ],
            'value' => fake()->randomFloat(2, 10, 200),
            'status' => fake()->randomElement(FormSubmission::STATUSES),
            'submission_time' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
