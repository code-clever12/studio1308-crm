@extends('emails.layout')

@section('subject', __('Your appointment has been cancelled'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $appointment->customer->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __('Your appointment for :service on :date has been cancelled.', [
            'service' => $appointment->service->name,
            'date' => $appointment->appointment_date->toFormattedDateString(),
        ]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:24px;">
        @if ((float) ($appointment->cancellation_fee ?? 0) > 0)
            <tr>
                <td style="padding:4px 0; color:#6b6155;">{{ __('Cancellation fee') }}</td>
                <td style="padding:4px 0; text-align:right;">${{ number_format((float) $appointment->cancellation_fee, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; font-weight:600;">{{ __('Refund amount') }}</td>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; text-align:right; font-weight:600;">${{ number_format($refundAmount, 2) }}</td>
        </tr>
    </table>

    <p style="margin:0 0 24px; font-size:13px; color:#6b6155;">
        {{ __('Refunds typically appear on your statement within 5-10 business days.') }}
    </p>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('customer.booking.create') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('Book Another Appointment') }}
        </a>
    </p>
@endsection
