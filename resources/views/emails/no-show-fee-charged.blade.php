@extends('emails.layout')

@section('subject', __('Receipt: no-show fee charged'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $appointment->customer->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __("You missed your appointment for :service on :date, so per our no-show policy a fee has been charged to the card on file.", [
            'service' => $appointment->service->name,
            'date' => $appointment->appointment_date->toFormattedDateString(),
        ]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:24px;">
        <tr>
            <td style="padding:8px 0; border-top:1px solid #e2ece4; font-weight:600;">{{ __('No-show fee charged') }}</td>
            <td style="padding:8px 0; border-top:1px solid #e2ece4; text-align:right; font-weight:600;">${{ number_format($feeAmount, 2) }}</td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('customer.appointments.index') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View My Appointments') }}
        </a>
    </p>
@endsection
