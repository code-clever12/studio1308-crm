@extends('emails.layout')

@section('subject', __('You received a tip!'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $tip->staff->user->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __('Nice work! A customer left you a tip.') }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f1; border-radius:8px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px; text-align:center;">
                <p style="margin:0; font-size:28px; font-weight:600; color:#1f3d2b;">${{ number_format((float) $tip->amount, 2) }}</p>
                @if ($tip->percentage)
                    <p style="margin:4px 0 0; font-size:12px; color:#6b6155;">{{ (float) $tip->percentage }}% {{ __('tip') }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('dashboard') }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View Dashboard') }}
        </a>
    </p>
@endsection
