<?php

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists only forms that have at least one submission', function () {
    $withSubmissions = Form::factory()->create();
    FormSubmission::factory()->count(3)->create(['form_id' => $withSubmissions->id]);

    $empty = Form::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.form-submissions.index'));

    $response->assertOk();
    $forms = $response->viewData('forms');
    expect($forms->pluck('id'))->toContain($withSubmissions->id)
        ->and($forms->pluck('id'))->not->toContain($empty->id)
        ->and($forms->firstWhere('id', $withSubmissions->id)->submissions_count)->toBe(3);
});

it('shows a form\'s submissions with dynamic payload columns', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->create([
        'form_id' => $form->id,
        'payload' => ['full_name' => 'Jane Doe', 'phone_number' => '555-1234'],
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.form-submissions.show', $form));

    $response->assertOk()->assertSee('Jane Doe')->assertSee('555-1234');
    expect($response->viewData('payloadKeys')->all())->toBe(['full_name', 'phone_number']);
    expect($response->viewData('submissions')->pluck('id'))->toContain($submission->id);
});

it('updates a submission\'s status', function () {
    $submission = FormSubmission::factory()->create(['status' => 'cold_lead']);

    $this->actingAs($this->admin)
        ->patch(route('admin.form-submissions.update-status', $submission), ['status' => 'warm_lead'])
        ->assertRedirect();

    expect($submission->fresh()->status)->toBe('warm_lead');
});

it('rejects an invalid status value', function () {
    $submission = FormSubmission::factory()->create(['status' => 'cold_lead']);

    $this->actingAs($this->admin)
        ->patch(route('admin.form-submissions.update-status', $submission), ['status' => 'bogus'])
        ->assertSessionHasErrors('status');

    expect($submission->fresh()->status)->toBe('cold_lead');
});

it('updates a submission\'s payload, value, url, and status via the edit form', function () {
    $submission = FormSubmission::factory()->create([
        'payload' => ['full_name' => 'Jane Doe', 'phone_number' => '555-1234'],
        'status' => 'cold_lead',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.form-submissions.edit', $submission))
        ->assertOk()
        ->assertSee('Jane Doe');

    $this->actingAs($this->admin)
        ->put(route('admin.form-submissions.update', $submission), [
            'payload' => ['full_name' => 'Jane Smith', 'phone_number' => '555-1234'],
            'value' => 75.5,
            'status' => 'hot_lead',
            'url' => 'https://example.com/landing',
        ])
        ->assertRedirect(route('admin.form-submissions.show', $submission->form));

    $submission->refresh();
    expect($submission->payload)->toBe(['full_name' => 'Jane Smith', 'phone_number' => '555-1234'])
        ->and((float) $submission->value)->toBe(75.5)
        ->and($submission->status)->toBe('hot_lead')
        ->and($submission->url)->toBe('https://example.com/landing');
});

it('deletes a submission', function () {
    $submission = FormSubmission::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.form-submissions.destroy', $submission))
        ->assertRedirect(route('admin.form-submissions.show', $submission->form));

    expect(FormSubmission::find($submission->id))->toBeNull();
});

it('blocks a non-admin from viewing, editing, or deleting form submissions', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

    $this->actingAs($customer)->get(route('admin.form-submissions.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.form-submissions.show', $form))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.form-submissions.edit', $submission))->assertForbidden();
    $this->actingAs($customer)->delete(route('admin.form-submissions.destroy', $submission))->assertForbidden();
});
