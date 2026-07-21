@extends('emails.layout')

@section('subject', __('Action needed: ACH payout failed'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi,') }}</p>

    <p style="margin:0 0 20px;">
        {{ __('An ACH payout to :name failed and needs attention.', ['name' => $payout->staff->user->name]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fdf2f2; border-radius:8px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <p style="margin:0 0 4px; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#b23a34;">{{ __('Payout amount') }}</p>
                <p style="margin:0 0 12px; font-size:18px; font-weight:600;">${{ number_format((float) $payout->amount, 2) }}</p>
                <p style="margin:0; font-size:13px; color:#6b6155;">{{ $payout->failure_reason }}</p>
            </td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('admin.payouts.index') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('Review Payouts') }}
        </a>
    </p>
@endsection
