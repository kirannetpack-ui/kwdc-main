@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            📋
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">New Loader Assignment</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">A new loading and unloading deployment has been scheduled.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Dispatch Order:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 700; text-align: right; font-family: monospace;">#{{ $assignment->dispatch_id }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Scheduled Date:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $assignment->assignment_date ? $assignment->assignment_date->format('F j, Y') : date('F j, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Reporting Time:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $assignment->start_time ? $assignment->start_time->format('g:i A') : 'Scheduled' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Required Personnel:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 600; text-align: right;">{{ $assignment->required_loaders ?? 1 }} Loader(s)</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Status:</td>
                <td style="padding: 7px 0; text-align: right;">
                    <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                        {{ ucfirst($assignment->status ?? 'Assigned') }}
                    </span>
                </td>
            </tr>
            @if(!empty($assignment->notes))
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Operational Notes:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $assignment->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/login') }}" style="display: inline-block; background: #d96b43; color: white; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 600; font-size: 14px;">Open Dispatch Portal →</a>
    </div>
</div>
@endsection