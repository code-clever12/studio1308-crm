<?php

use App\Models\Form;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists forms ordered by name', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Form::factory()->create(['name' => 'Zeta Form']);
    Form::factory()->create(['name' => 'Alpha Form']);

    $response = $this->getJson(route('api.v1.forms.index'));

    $response->assertOk();
    expect($response->json('data.0.name'))->toBe('Alpha Form');
});

it('requires authentication', function () {
    $this->getJson(route('api.v1.forms.index'))->assertUnauthorized();
});
