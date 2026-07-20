<?php

use App\Models\ConsentForm;
use App\Models\Salon;
use App\Models\User;

beforeEach(function () {
    Salon::factory()->create();
    $this->admin = User::factory()->admin()->create();
});

it('lets an admin create a consent form with dynamic fields', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.forms.store'), [
        'name' => 'Allergy Waiver',
        'description' => 'Required before chemical services.',
        'is_active' => true,
        'fields' => [
            ['id' => 'allergies', 'label' => 'Any allergies?', 'type' => 'text', 'required' => true],
            ['id' => 'agree', 'label' => 'I consent', 'type' => 'checkbox', 'required' => true],
        ],
    ]);

    $response->assertRedirect(route('admin.forms.index'));

    $form = ConsentForm::where('name', 'Allergy Waiver')->first();
    expect($form)->not->toBeNull()
        ->and($form->fields_json)->toHaveCount(2);
});

it('lets an admin update a consent form', function () {
    $form = ConsentForm::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)->put(route('admin.forms.update', $form), [
        'name' => 'New Name',
        'is_active' => true,
        'fields' => [['id' => 'a', 'label' => 'A', 'type' => 'text', 'required' => false]],
    ])->assertRedirect(route('admin.forms.index'));

    expect($form->fresh()->name)->toBe('New Name');
});

it('deactivates a consent form on destroy', function () {
    $form = ConsentForm::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->delete(route('admin.forms.destroy', $form))
        ->assertRedirect(route('admin.forms.index'));

    expect($form->fresh()->is_active)->toBeFalse();
});
