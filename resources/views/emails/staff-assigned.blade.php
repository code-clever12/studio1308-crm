@extends('emails.layout')

@section('subject', __('New appointment assigned to you'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $appointment->staff->user->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __("You've got a new appointment on your schedule.") }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f1; border-radius:8px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <p style="margin:0 0 4px; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#9c7a2e;">{{ $appointment->service->name }}</p>
                <p style="margin:0 0 8px; font-size:18px; font-weight:600;">
                    {{ $appointment->appointment_date->toFormattedDateString() }} {{ __('at') }} {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('g:i A') }}
                </p>
                <p style="margin:0; color:#3a6d55;">{{ __('Customer: :name', ['name' => $appointment->customer->name]) }}</p>
            </td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('dashboard') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View Dashboard') }}
        </a>
    </p>
@endsection
