@extends('emails.layout')

@section('subject', __("You're confirmed"))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $appointment->customer->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __("You're all set! Here's your appointment confirmation.") }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f1; border-radius:8px; margin-bottom:20px;">
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

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:24px;">
        <tr>
            <td style="padding:4px 0; color:#6b6155;">{{ __('Service total') }}</td>
            <td style="padding:4px 0; text-align:right;">${{ number_format((float) $appointment->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0; color:#6b6155;">{{ __('Deposit paid') }}</td>
            <td style="padding:4px 0; text-align:right;">${{ number_format((float) $appointment->deposit_paid, 2) }}</td>
        </tr>
        @if ($appointment->tip_amount > 0)
            <tr>
                <td style="padding:4px 0; color:#6b6155;">{{ __('Tip') }}</td>
                <td style="padding:4px 0; text-align:right;">${{ number_format((float) $appointment->tip_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; font-weight:600;">{{ __('Balance due at checkout') }}</td>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; text-align:right; font-weight:600;">${{ number_format((float) $appointment->remaining_balance, 2) }}</td>
        </tr>
    </table>

    <p style="text-align:center; margin:0 0 24px;">
        <a href="{{ route('customer.appointments.index') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View My Appointment') }}
        </a>
    </p>

    @if (! empty($salon?->cancellation_policy))
        <p style="margin:0; font-size:12px; color:#6b6155;">
            <strong>{{ __('Cancellation policy:') }}</strong> {{ $salon->cancellation_policy }}
        </p>
    @endif

    <p style="margin:16px 0 0; font-size:12px; color:#6b6155;">
        {{ __("A calendar invite is attached to this email.") }}
    </p>
@endsection
