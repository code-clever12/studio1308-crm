@extends('emails.layout')

@section('subject', __('A spot just opened up!'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $waitlist->customer->name]) }}</p>

    <p style="margin:0 0 20px;">
        {{ __('Good news — a slot just opened up for :service on :date.', [
            'service' => $waitlist->service->name,
            'date' => $waitlist->requested_date->toFormattedDateString(),
        ]) }}
        @if ($waitlist->staff)
            {{ __('with :name.', ['name' => $waitlist->staff->user->name]) }}
        @endif
    </p>

    <p style="margin:0 0 24px; font-size:13px; color:#6b6155;">
        {{ __('This offer expires in 48 hours, so grab it while you can.') }}
    </p>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('customer.booking.create', ['service' => $waitlist->service_id]) }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('Book This Slot') }}
        </a>
    </p>
@endsection
