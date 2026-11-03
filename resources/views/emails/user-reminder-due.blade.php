@extends('emails.layout')

@section('content')
    <h2>{{ $reminder->title ?? 'Scheduled Reminder' }}</h2>
    <p>Hello {{ $reminder->user->name ?? 'Valued Partner' }},</p>
    <p>This is your KTM-WDC reminder for {{ isset($reminder->starts_at) && is_object($reminder->starts_at) ? $reminder->starts_at->format('M d, Y h:i A') : (is_string($reminder->starts_at ?? null) ? $reminder->starts_at : 'Today') }}.</p>
    @if(!empty($reminder->notes))
        <p>{{ $reminder->notes }}</p>
    @endif
    <p>
        <a href="{{ route('reminders.index') }}" style="display:inline-block;background:#d96b43;color:white;padding:10px 16px;border-radius:6px;text-decoration:none;font-weight:600;">
            Open reminders
        </a>
    </p>
@endsection
