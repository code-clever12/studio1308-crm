@extends('emails.layout')

@section('subject', __('Daily summary'))

@section('content')
    <p style="margin:0 0 20px;">
        {{ __("Here's how :date went.", ['date' => $stats['date']]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:24px;">
        <tr>
            <td style="padding:6px 0; color:#6b6155;">{{ __('Appointments') }}</td>
            <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $stats['appointments_count'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#6b6155;">{{ __('No-shows') }}</td>
            <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $stats['no_shows_count'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#6b6155;">{{ __('Cancellations') }}</td>
            <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $stats['cancellations_count'] }}</td>
        </tr>
        <tr>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; font-weight:600;">{{ __('Revenue') }}</td>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; text-align:right; font-weight:600;">${{ number_format((float) $stats['revenue'], 2) }}</td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View Full Dashboard') }}
        </a>
    </p>
@endsection
