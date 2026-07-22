@extends('emails.layout')

@section('subject', __('New lead: :form', ['form' => $submission->form->name]))

@section('content')
    <p style="margin:0 0 20px;">
        {{ __('You received a new lead from :form.', ['form' => $submission->form->name]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:24px;">
        @foreach ($submission->payload ?? [] as $key => $value)
            <tr>
                <td style="padding:6px 0; color:#6b6155; text-transform:capitalize;">{{ str_replace('_', ' ', $key) }}</td>
                <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $value }}</td>
            </tr>
        @endforeach
        @if ($submission->value !== null)
            <tr>
                <td style="padding:6px 0; color:#6b6155;">{{ __('Value') }}</td>
                <td style="padding:6px 0; text-align:right; font-weight:600;">${{ number_format((float) $submission->value, 2) }}</td>
            </tr>
        @endif
        @if ($submission->url)
            <tr>
                <td style="padding:6px 0; color:#6b6155;">{{ __('Source') }}</td>
                <td style="padding:6px 0; text-align:right; font-weight:600; word-break:break-all;">{{ $submission->url }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; color:#6b6155;">{{ __('Submitted') }}</td>
            <td style="padding:8px 0 0; border-top:1px solid #e2ece4; text-align:right; font-weight:600;">{{ $submission->submission_time->format('M j, Y g:i A') }}</td>
        </tr>
    </table>

    <p style="text-align:center; margin:0;">
        <a href="{{ route('admin.form-submissions.show', $submission->form) }}" style="display:inline-block; background-color:#1f3d2b; color:#f6f3ec; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:14px; font-weight:600;">
            {{ __('View in Dashboard') }}
        </a>
    </p>
@endsection
