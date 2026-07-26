<?php

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Notifications\NewFormSubmissionReceived;
use Illuminate\Support\Facades\Notification;

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
        ->and($submission->status)->toBe('cold_lead')
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

it('marks a partial submission as abandoned and does not notify admins', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $response = $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-123',
        'is_partial' => true,
        'full_name' => 'Jane Doe',
    ]);

    $response->assertCreated();

    $submission = FormSubmission::where('draft_id', 'draft-123')->first();
    expect($submission)->not->toBeNull()
        ->and($submission->capture_status)->toBe('abandoned');

    Notification::assertNotSentTo($admin, NewFormSubmissionReceived::class);
});

it('updates the same row instead of duplicating on a repeat draft_id submission', function () {
    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-123',
        'is_partial' => true,
        'full_name' => 'Jane Doe',
    ])->assertCreated();

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-123',
        'is_partial' => true,
        'full_name' => 'Jane Doe',
        'phone_number' => '555-1234',
    ])->assertCreated();

    expect(FormSubmission::where('draft_id', 'draft-123')->count())->toBe(1);

    $submission = FormSubmission::where('draft_id', 'draft-123')->first();
    expect($submission->payload)->toBe([
        'full_name' => 'Jane Doe',
        'phone_number' => '555-1234',
    ]);
});

it('marks the draft completed and notifies admins once the final submission arrives', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-456',
        'is_partial' => true,
        'full_name' => 'Jane Doe',
    ])->assertCreated();

    Notification::assertNotSentTo($admin, NewFormSubmissionReceived::class);

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-456',
        'is_partial' => false,
        'full_name' => 'Jane Doe',
        'phone_number' => '555-1234',
    ])->assertCreated();

    expect(FormSubmission::where('draft_id', 'draft-456')->count())->toBe(1);

    $submission = FormSubmission::where('draft_id', 'draft-456')->first();
    expect($submission->capture_status)->toBe('completed');

    Notification::assertSentTo($admin, NewFormSubmissionReceived::class);
});

it('preserves an admin-assigned lead status across repeat draft submissions', function () {
    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-789',
        'is_partial' => true,
        'full_name' => 'Jane Doe',
    ])->assertCreated();

    $submission = FormSubmission::where('draft_id', 'draft-789')->first();
    $submission->update(['status' => 'hot_lead']);

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'booking-wizard',
        'draft_id' => 'draft-789',
        'is_partial' => false,
        'full_name' => 'Jane Doe',
    ])->assertCreated();

    expect($submission->fresh()->status)->toBe('hot_lead');
});

it('defaults capture_status to completed and still notifies when is_partial is omitted entirely', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $this->postJson(route('api.v1.submit-form'), [
        'form_slug' => 'summer-offer-20',
        'full_name' => 'No Partial Flag',
    ])->assertCreated();

    $submission = FormSubmission::latest('id')->first();
    expect($submission->capture_status)->toBe('completed');

    Notification::assertSentTo($admin, NewFormSubmissionReceived::class);
});
