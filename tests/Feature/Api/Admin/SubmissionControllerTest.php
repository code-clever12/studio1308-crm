<?php

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists submissions with pagination metadata', function () {
    Sanctum::actingAs($this->admin);
    FormSubmission::factory()->count(25)->create();

    $response = $this->getJson(route('api.v1.submissions.index'));

    $response->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonPath('meta.total', 25)
        ->assertJsonPath('meta.last_page', 2);
});

it('filters submissions by form, service, and capture status', function () {
    Sanctum::actingAs($this->admin);

    $formA = Form::factory()->create();
    $formB = Form::factory()->create();

    $match = FormSubmission::factory()->create([
        'form_id' => $formA->id,
        'service' => 'lashes',
        'capture_status' => 'abandoned',
    ]);
    $other = FormSubmission::factory()->create([
        'form_id' => $formB->id,
        'service' => 'hair',
        'capture_status' => 'completed',
    ]);

    $ids = fn ($response) => collect($response->json('data'))->pluck('id');

    $byForm = $this->getJson(route('api.v1.submissions.index', ['form_id' => $formA->id]));
    expect($ids($byForm))->toContain($match->id)->not->toContain($other->id);

    $byService = $this->getJson(route('api.v1.submissions.index', ['service' => 'lashes']));
    expect($ids($byService))->toContain($match->id)->not->toContain($other->id);

    $byCapture = $this->getJson(route('api.v1.submissions.index', ['capture_status' => 'abandoned']));
    expect($ids($byCapture))->toContain($match->id)->not->toContain($other->id);
});

it('shows a single submission', function () {
    Sanctum::actingAs($this->admin);
    $submission = FormSubmission::factory()->create(['payload' => ['full_name' => 'Jane Doe']]);

    $this->getJson(route('api.v1.submissions.show', $submission))
        ->assertOk()
        ->assertJsonPath('data.id', $submission->id)
        ->assertJsonPath('data.payload.full_name', 'Jane Doe');
});

it('updates a submission', function () {
    Sanctum::actingAs($this->admin);
    $submission = FormSubmission::factory()->create(['status' => 'cold_lead']);

    $this->putJson(route('api.v1.submissions.update', $submission), [
        'payload' => ['full_name' => 'Jane Smith'],
        'value' => 99.5,
        'status' => 'hot_lead',
        'url' => 'https://example.com',
    ])->assertOk()->assertJsonPath('data.status', 'hot_lead');

    $submission->refresh();
    expect($submission->payload)->toBe(['full_name' => 'Jane Smith'])
        ->and((float) $submission->value)->toBe(99.5)
        ->and($submission->status)->toBe('hot_lead');
});

it('updates just the status', function () {
    Sanctum::actingAs($this->admin);
    $submission = FormSubmission::factory()->create(['status' => 'cold_lead']);

    $this->patchJson(route('api.v1.submissions.update-status', $submission), ['status' => 'warm_lead'])
        ->assertOk()->assertJsonPath('data.status', 'warm_lead');

    expect($submission->fresh()->status)->toBe('warm_lead');
});

it('rejects an invalid status', function () {
    Sanctum::actingAs($this->admin);
    $submission = FormSubmission::factory()->create(['status' => 'cold_lead']);

    $this->patchJson(route('api.v1.submissions.update-status', $submission), ['status' => 'bogus'])
        ->assertStatus(422);

    expect($submission->fresh()->status)->toBe('cold_lead');
});

it('deletes a submission', function () {
    Sanctum::actingAs($this->admin);
    $submission = FormSubmission::factory()->create();

    $this->deleteJson(route('api.v1.submissions.destroy', $submission))
        ->assertOk()->assertJson(['success' => true]);

    expect(FormSubmission::find($submission->id))->toBeNull();
});

it('exports submissions as CSV', function () {
    Sanctum::actingAs($this->admin);
    FormSubmission::factory()->create(['payload' => ['full_name' => 'Jane Doe']]);

    $response = $this->get(route('api.v1.submissions.export'));

    $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->toContain('Jane Doe');
});

it('lists distinct services', function () {
    Sanctum::actingAs($this->admin);
    FormSubmission::factory()->create(['service' => 'lashes']);
    FormSubmission::factory()->create(['service' => 'hair']);
    FormSubmission::factory()->create(['service' => null]);

    $response = $this->getJson(route('api.v1.submissions.services'));

    $response->assertOk();
    expect($response->json('data'))->toContain('hair', 'lashes');
});

it('requires authentication', function () {
    $this->getJson(route('api.v1.submissions.index'))->assertUnauthorized();
});

it('blocks a non-admin role', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    Sanctum::actingAs($staff);

    $this->getJson(route('api.v1.submissions.index'))->assertForbidden();
});
