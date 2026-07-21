@extends('emails.layout')

@section('subject', __('Reminder: your appointment is tomorrow'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $appointment->customer->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __('Just a reminder — your appointment is coming up tomorrow.') }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f1; border-radius:8px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <p style="margin:0 0 4px; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#9c7a2e;">{{ $appointment->service->name }}</p>
                <p style="margin:0 0 12px; font-size:18px; font-weight:600;">
                    {{ $appointment->appointment_date->toFormattedDateString() }} {{ __('at') }} {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('g:i A') }}
                </p>
                @if ($appointment->staff)
                    <p style="margin:0; color:#3a6d55;">{{ __('with :name', ['name' => $appointment->staff->user->name]) }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="text-align:center; margin:0 0 24px;">
        <a href="{{ route('customer.appointments.index') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('Confirm, Reschedule, or Cancel') }}
        </a>
    </p>

    @if (! empty($salon))
        <p style="margin:0; font-size:12px; color:#6b6155;">
            {{ trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,') }}
            @if ($salon->phone)
                &middot; {{ $salon->phone }}
            @endif
        </p>
    @endif
@endsection
