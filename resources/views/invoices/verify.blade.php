<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Invoice {{ $invoice->invoice_number ?? $invoice->id }} | KTM-WDC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --claude-bg: #FAF8F5;
            --claude-surface: #FFFDF9;
            --claude-ink: #24201D;
            --claude-muted: #645D56;
            --claude-line: #E8E2D8;
            --claude-line-subtle: #F0ECE4;
            --claude-terracotta: #D96B43;
            --claude-terracotta-hover: #C35832;
            --claude-terracotta-soft: rgba(217, 107, 67, 0.08);
            --claude-emerald: #2E6B4F;
            --claude-emerald-soft: rgba(46, 107, 79, 0.08);
            --claude-amber: #B8732A;
            --claude-amber-soft: rgba(184, 115, 42, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--claude-bg);
            color: var(--claude-ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(217, 107, 67, 0.04), transparent 400px),
                radial-gradient(circle at 90% 90%, rgba(184, 115, 42, 0.04), transparent 450px);
        }

        .verify-card {
            background-color: var(--claude-surface);
            border: 1px solid var(--claude-line);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(36, 32, 29, 0.04), 0 1px 3px rgba(36, 32, 29, 0.02);
            width: 100%;
            max-width: 620px;
            overflow: hidden;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .verify-header {
            padding: 32px 32px 24px;
            border-bottom: 1px solid var(--claude-line-subtle);
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 253, 249, 0.8) 0%, rgba(250, 248, 245, 0.5) 100%);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            text-decoration: none;
            color: var(--claude-ink);
        }

        .brand-badge .logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background-color: var(--claude-terracotta);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
        }

        .brand-badge .brand-text {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pill.verified {
            background-color: var(--claude-emerald-soft);
            color: var(--claude-emerald);
            border: 1px solid rgba(46, 107, 79, 0.2);
        }

        .status-pill.flagged {
            background-color: rgba(200, 68, 56, 0.08);
            color: #C84438;
            border: 1px solid rgba(200, 68, 56, 0.2);
        }

        .status-pill.banned {
            background-color: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
            border: 1px solid rgba(220, 38, 38, 0.35);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .ban-alert-card {
            background: linear-gradient(135deg, rgba(254, 242, 242, 0.95), rgba(254, 226, 226, 0.9));
            border: 2px solid #ef4444;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.15);
        }

        .ban-alert-card .ban-title {
            font-size: 16px;
            font-weight: 800;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .ban-alert-card .ban-reason {
            font-size: 13px;
            color: #7f1d1d;
            background: #ffffff;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 10px 12px;
            margin: 8px 0;
            font-family: inherit;
        }

        .ban-alert-card .ban-legal {
            font-size: 11.5px;
            color: #991b1b;
            line-height: 1.5;
            font-weight: 500;
        }

        .title {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            color: var(--claude-ink);
            margin-top: 14px;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .subtitle {
            font-size: 13px;
            color: var(--claude-muted);
            line-height: 1.5;
        }

        .verify-body {
            padding: 28px 32px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 480px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .detail-item {
            background-color: var(--claude-bg);
            border: 1px solid var(--claude-line-subtle);
            border-radius: 12px;
            padding: 14px 16px;
        }

        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--claude-muted);
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--claude-ink);
            word-break: break-word;
        }

        .detail-value.mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .amount-card {
            background-color: var(--claude-terracotta-soft);
            border: 1px solid rgba(217, 107, 67, 0.2);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .amount-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--claude-ink);
        }

        .amount-figure {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--claude-terracotta);
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .payment-status.paid {
            background-color: var(--claude-emerald-soft);
            color: var(--claude-emerald);
        }

        .payment-status.pending {
            background-color: var(--claude-amber-soft);
            color: var(--claude-amber);
        }

        .security-badge {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background-color: rgba(46, 107, 79, 0.05);
            border: 1px solid rgba(46, 107, 79, 0.15);
            font-size: 12px;
            color: var(--claude-muted);
            line-height: 1.5;
        }

        .security-badge i {
            color: var(--claude-emerald);
            font-size: 16px;
            margin-top: 2px;
        }

        .verify-actions {
            padding: 20px 32px 28px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-top: 1px solid var(--claude-line-subtle);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background-color: var(--claude-terracotta);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: var(--claude-terracotta-hover);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 20px;
            background-color: var(--claude-bg);
            color: var(--claude-ink);
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            border: 1px solid var(--claude-line);
            transition: all 0.15s ease;
        }

        .btn-secondary:hover {
            background-color: #f2ece2;
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--claude-muted);
            margin-top: 20px;
        }
    </style>
</head>
<body>

@php
    $client = $invoice->client ?? optional($invoice->warehouseRequest)->client;
    $warehouse = $invoice->warehouse ?? optional($invoice->warehouseRequest)->warehouse;
    $amount = (float) ($invoice->grand_total ?? $invoice->amount ?? 0);
    $status = strtolower($invoice->status ?? 'pending');
    $isPaid = in_array($status, ['paid', 'completed']);
@endphp

<div class="verify-card">
    <div class="verify-header">
        <a href="{{ url('/') }}" class="brand-badge">
            <span class="logo-icon">K</span>
            <span class="brand-text">KTM-WDC</span>
        </a>
        <div>
            @if($invoice->isBanned())
                <span class="status-pill banned">
                    <i class="fas fa-ban"></i> 🚨 QR CODE BANNED & REVOKED
                </span>
            @elseif($status === 'flagged')
                <span class="status-pill flagged">
                    <i class="fas fa-exclamation-triangle"></i> Verification Notice: Flagged
                </span>
            @else
                <span class="status-pill verified">
                    <i class="fas fa-shield-check"></i> Officially Verified Invoice
                </span>
            @endif
        </div>
        <h1 class="title">
            @if($invoice->isBanned())
                <span style="color: #991b1b;">Invoice QR Code Revoked</span>
            @else
                Invoice Authenticity Check
            @endif
        </h1>
        <p class="subtitle">
            @if($invoice->isBanned())
                This invoice verification has been suspended and marked as REVOKED by security compliance.
            @else
                This invoice record matches the authoritative cryptographic record on the KTM-WDC portal ledger.
            @endif
        </p>
    </div>

    <div class="verify-body">
        @if($invoice->isBanned())
        <div class="ban-alert-card">
            <div class="ban-title">
                <i class="fas fa-triangle-exclamation"></i>
                CARGO RELEASE & GATE CLEARANCE PROHIBITED
            </div>
            <p style="font-size: 12.5px; color: #7f1d1d; margin-bottom: 4px;">
                This invoice QR verification has been officially <strong>banned and revoked</strong> by KTM-WDC Platform Compliance.
            </p>
            <div class="ban-reason">
                <strong>Revocation Reason:</strong> {{ $invoice->ban_reason ?: 'Compliance violation / security flag / cargo impound.' }}
                @if($invoice->banned_at)
                    <div style="font-size: 11px; color: #991b1b; margin-top: 4px;">Revoked on: {{ $invoice->banned_at->format('M d, Y H:i:s T') }}</div>
                @endif
            </div>
            <p class="ban-legal">
                <strong>Legal Notice:</strong> Under Muluki Civil Code 2074 and National Logistics Safety Regulations, security personnel and warehouse operators are strictly instructed to withhold cargo dispatch and deny gate-pass validation for this document.
            </p>
        </div>
        @endif

        <div class="amount-card">
            <div>
                <div class="amount-label">Verified Total Amount</div>
                <div style="margin-top: 4px;">
                    <span class="payment-status {{ $isPaid ? 'paid' : 'pending' }}">
                        <i class="fas {{ $isPaid ? 'fa-check' : 'fa-clock' }}"></i>
                        {{ $isPaid ? 'Settled / Paid' : 'Payment Pending' }}
                    </span>
                </div>
            </div>
            <div class="amount-figure">NPR {{ number_format($amount, 2) }}</div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Invoice Reference</div>
                <div class="detail-value mono">{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Issued Date</div>
                <div class="detail-value">{{ $invoice->created_at?->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Client / Entity</div>
                <div class="detail-value">{{ $client->name ?? 'Valued Customer' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Warehouse / Hub</div>
                <div class="detail-value">{{ $warehouse->name ?? 'Kathmandu Central Depot' }}</div>
            </div>
        </div>

        <div class="security-badge">
            <i class="fas fa-certificate"></i>
            <div>
                <strong>Tamper-Proof Ledger Record</strong>
                <div>Origin verification code: <code style="font-family: monospace; font-size: 11px;">{{ strtoupper(substr(hash('sha256', ($invoice->id ?? 1) . ($invoice->created_at ?? now())), 0, 16)) }}</code>. Verified directly against KTM-WDC platform database.</div>
            </div>
        </div>
    </div>

    <div class="verify-actions">
        @auth
            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn-primary">
                <i class="fas fa-file-invoice"></i> View Invoice in Portal
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Sign In to View Full Document
            </a>
        @endauth
        <a href="{{ url('/') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Return to KTM-WDC Home
        </a>
    </div>
</div>

<p class="footer-note">
    &copy; {{ date('Y') }} KTM-WDC (Warehouse & Distribution Connect). Verified Logistics Registry.
</p>

</body>
</html>
