<?php

use App\Models\Salon;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->salon = Salon::factory()->create();
});

it('lets an admin update salon settings', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'name' => $this->salon->name,
        'address' => $this->salon->address,
        'city' => $this->salon->city,
        'state' => $this->salon->state,
        'zip_code' => $this->salon->zip_code,
        'phone' => $this->salon->phone,
        'email' => $this->salon->email,
        'timezone' => $this->salon->timezone,
        'opens_at' => '08:00',
        'closes_at' => '20:00',
        'deposit_percentage' => 30,
        'no_show_fee' => 35,
        'enable_tips' => true,
        'sales_tax_rate' => 9.5,
    ]);

    $response->assertRedirect(route('admin.settings.edit'));

    $this->salon->refresh();
    expect((float) $this->salon->deposit_percentage)->toBe(30.0)
        ->and((float) $this->salon->no_show_fee)->toBe(35.0);
});

it('rejects closing time before opening time', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'name' => $this->salon->name,
        'address' => $this->salon->address,
        'city' => $this->salon->city,
        'state' => $this->salon->state,
        'zip_code' => $this->salon->zip_code,
        'phone' => $this->salon->phone,
        'email' => $this->salon->email,
        'timezone' => $this->salon->timezone,
        'opens_at' => '18:00',
        'closes_at' => '09:00',
        'deposit_percentage' => 25,
        'no_show_fee' => 25,
    ])->assertSessionHasErrors('closes_at');
});

it('saves a comma-separated list of extra lead notification emails', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'name' => $this->salon->name,
        'address' => $this->salon->address,
        'city' => $this->salon->city,
        'state' => $this->salon->state,
        'zip_code' => $this->salon->zip_code,
        'phone' => $this->salon->phone,
        'email' => $this->salon->email,
        'lead_notification_emails' => 'sales@example.com, owner@example.com',
        'timezone' => $this->salon->timezone,
        'opens_at' => '08:00',
        'closes_at' => '20:00',
        'deposit_percentage' => 30,
        'no_show_fee' => 35,
    ]);

    $response->assertRedirect(route('admin.settings.edit'));

    $this->salon->refresh();
    expect($this->salon->leadNotificationEmails())->toBe(['sales@example.com', 'owner@example.com']);
});

it('rejects an invalid address in the lead notification emails list', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'name' => $this->salon->name,
        'address' => $this->salon->address,
        'city' => $this->salon->city,
        'state' => $this->salon->state,
        'zip_code' => $this->salon->zip_code,
        'phone' => $this->salon->phone,
        'email' => $this->salon->email,
        'lead_notification_emails' => 'sales@example.com, not-an-email',
        'timezone' => $this->salon->timezone,
        'opens_at' => '08:00',
        'closes_at' => '20:00',
        'deposit_percentage' => 30,
        'no_show_fee' => 35,
    ])->assertSessionHasErrors('lead_notification_emails');
});
