@extends('emails.layout')

@section('content')
<div class="animate-fade">
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 50%; width: 56px; height: 56px; line-height: 56px; font-size: 28px; margin-bottom: 12px;">
            🛡️
        </div>
        <h2 style="color: #1f2937; margin: 0 0 6px; font-size: 22px;">Warehouse Request – Insurance Documents</h2>
        <p style="color: #6b7280; font-size: 14px; margin: 0;">New commercial cargo coverage inquiry initiated through KTM-WDC.</p>
    </div>

    <div style="background: #fdfbf7; border: 1px solid #f1e9dc; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Client Name:</td>
                <td style="padding: 7px 0; color: #1f2937; font-weight: 600; text-align: right;">{{ $request->client->name ?? 'Valued Client' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Client Email:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $request->client->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Required Space:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $request->required_space ?? 'N/A' }} m³</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Lease Duration:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ $request->duration_months ?? 1 }} Month(s)</td>
            </tr>
            <tr>
                <td style="padding: 7px 0; color: #6b7280;">Coverage Term:</td>
                <td style="padding: 7px 0; color: #1f2937; text-align: right;">{{ now()->format('d M Y') }} – {{ now()->addMonths($request->duration_months ?? 1)->format('d M Y') }}</td>
            </tr>
        </table>
    </div>

    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
        <p style="margin: 0; color: #1e40af; font-size: 13px; line-height: 1.5;">
            <strong>Underwriting Notice:</strong><br>
            Attached are the associated commercial invoice and packing manifesto. Please review underwriting terms and return certificate endorsement.
        </p>
    </div>
</div>
@endsection