<?php

use App\Models\Form;
use App\Models\FormSubmission;

it('creates a new Form and stores the submission with payload/utm/value split out', function () {
    $response = $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'summer-offer-20',
        'form_name' => 'Summer Offer 20%',
        'url' => 'https://someuser.github.io/hair-salon-savannah/',
        'value' => 49.99,
        'utm_source' => 'facebook',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'summer2026',
        'full_name' => 'Jane Doe',
        'phone_number' => '555-1234',
        'hair_type' => 'curly',
    ]);

    $response->assertCreated()->assertJson(['success' => true]);

    $form = Form::where('slug', 'summer-offer-20')->first();
    expect($form)->not->toBeNull()
        ->and($form->name)->toBe('Summer Offer 20%');

    $submission = FormSubmission::where('form_id', $form->id)->first();
    expect($submission)->not->toBeNull()
        ->and($submission->payload)->toBe([
            'full_name' => 'Jane Doe',
            'phone_number' => '555-1234',
            'hair_type' => 'curly',
        ])
        ->and($submission->utm_parameters)->toBe([
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'summer2026',
        ])
        ->and((float) $submission->value)->toBe(49.99)
        ->and($submission->url)->toBe('https://someuser.github.io/hair-salon-savannah/')
        ->and($submission->status)->toBe('new')
        ->and($submission->submission_time)->not->toBeNull();
});

it('reuses an existing Form by slug instead of creating a duplicate', function () {
    $form = Form::factory()->create(['slug' => 'summer-offer-20']);

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'summer-offer-20',
        'full_name' => 'Second Lead',
    ])->assertCreated();

    expect(Form::where('slug', 'summer-offer-20')->count())->toBe(1)
        ->and($form->fresh()->submissions()->count())->toBe(1);
});

it('derives a readable form name from the slug when form_name is not given', function () {
    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'new-client-special',
        'full_name' => 'Jane Doe',
    ])->assertCreated();

    expect(Form::where('slug', 'new-client-special')->first()->name)->toBe('New Client Special');
});

it('rejects a submission with no form_slug', function () {
    $this->postJson(route('api.v1.submit-form'), [
        'full_name' => 'No Slug Given',
    ])->assertStatus(422)->assertJsonValidationErrors('form_slug');
});

it('sends permissive CORS headers for cross-origin browser requests', function () {
    $response = $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'summer-offer-20',
    ], ['Origin' => 'https://someuser.github.io']);

    $response->assertHeader('Access-Control-Allow-Origin', '*');
});
