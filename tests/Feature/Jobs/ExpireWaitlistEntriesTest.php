<?php

use App\Jobs\ExpireWaitlistEntries;
use App\Models\Waitlist;
use Carbon\Carbon;

it('expires notified waitlist entries older than 48 hours', function () {
    $stale = Waitlist::factory()->create([
        'status' => 'notified',
        'notification_sent_at' => Carbon::now()->subHours(49),
    ]);

    $recent = Waitlist::factory()->create([
        'status' => 'notified',
        'notification_sent_at' => Carbon::now()->subHours(10),
    ]);

    $stillWaiting = Waitlist::factory()->create([
        'status' => 'waiting',
        'notification_sent_at' => null,
    ]);

    (new ExpireWaitlistEntries)->handle();

    expect($stale->fresh()->status)->toBe('expired')
        ->and($recent->fresh()->status)->toBe('notified')
        ->and($stillWaiting->fresh()->status)->toBe('waiting');
});
