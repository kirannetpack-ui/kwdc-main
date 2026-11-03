<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'KTM-WDC Official Certificate')</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-accent {
            color: #ea580c;
        }
        .brand-sub {
            font-size: 11px;
            font-weight: 700;
            color: #f97316;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0 4px 0;
        }
        .brand-meta {
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
        }
        .doc-badge-table {
            border-collapse: collapse;
            float: right;
            text-align: right;
        }
        .doc-badge-table td {
            text-align: right;
            padding: 1px 0;
        }
        .doc-title-text {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .doc-ref-text {
            font-size: 10px;
            font-weight: 700;
            color: #ea580c;
        }
        .doc-date-text {
            font-size: 9px;
            color: #64748b;
        }
        .accent-bar {
            width: 100%;
            height: 3px;
            background: #ea580c;
            margin-top: 14px;
            margin-bottom: 22px;
        }
        .section-header {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 18px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .content-table th {
            width: 28%;
            background-color: #f8fafc;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
        }
        .content-table td {
            background-color: #ffffff;
            color: #0f172a;
            font-size: 11px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
        }
        .status-approved { background-color: #16a34a; }
        .status-pending { background-color: #d97706; }
        .status-rejected { background-color: #dc2626; }
        .status-completed { background-color: #2563eb; }

        .tag-pill {
            display: inline-block;
            background-color: #f1f5f9;
            color: #334155;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 3px;
            border: 1px solid #e2e8f0;
        }
        .footer-seal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        .footer-seal-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .qr-box {
            display: inline-block;
            border: 1px dashed #cbd5e1;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 6px;
            text-align: left;
        }
        .signature-block {
            text-align: right;
        }
        .sign-line {
            display: inline-block;
            width: 160px;
            border-bottom: 1px solid #0f172a;
            margin-bottom: 4px;
        }
        .legal-notice {
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            margin-top: 25px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <h1 class="brand-title">KTM<span class="brand-accent">-WDC</span></h1>
                <div class="brand-sub">Warehouse & Distribution Center</div>
                <div class="brand-meta">
                    Govt Reg: Nepal Logistics Authority #NP-74291 &bull; PAN: 601294812<br>
                    Tinkune & Baluwatar Freight Corridors, Kathmandu, Nepal<br>
                    Support: +977-1-5912400 &bull; verification@kwdc.test
                </div>
            </td>
            <td style="width: 45%;">
                <table class="doc-badge-table">
                    <tr>
                        <td class="doc-title-text">@yield('doc_title', 'Official Certificate')</td>
                    </tr>
                    <tr>
                        <td class="doc-ref-text">REF: @yield('doc_ref', 'KWDC-OFFICIAL')</td>
                    </tr>
                    <tr>
                        <td class="doc-date-text">Issued: {{ now()->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="doc-date-text">Security Hash: {{ strtoupper(substr(md5((string) (($warehouse->id ?? $dispatch->id ?? '4'))), 0, 10)) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="accent-bar"></div>

    <!-- MAIN BODY -->
    <div>
        @yield('content')
    </div>

    <!-- OFFICIAL SEAL & SIGNATURES -->
    <table class="footer-seal-table">
        <tr>
            <td style="width: 50%;">
                <div class="qr-box">
                    <strong style="font-size: 9px; color: #0f172a; text-transform: uppercase;">[ DIGITAL AUTHENTICATION ]</strong><br>
                    <span style="font-size: 8px; color: #64748b;">
                        Verified by KTM-WDC Central Infrastructure Ledger<br>
                        Certificate Validity: Active &bull; Tamper-Proof Electronic Seal
                    </span>
                </div>
            </td>
            <td style="width: 50%;" class="signature-block">
                <div class="sign-line"></div><br>
                <strong style="font-size: 10px; color: #0f172a;">Authorized Registrar</strong><br>
                <span style="font-size: 9px; color: #64748b;">KTM-WDC Commercial Facility Operations</span>
            </td>
        </tr>
    </table>

    <div class="legal-notice">
        This document is an authenticated certificate issued pursuant to the Nepal Electronic Transactions Act (2063) and commercial warehouse registry standards under the Muluki Civil Code 2074. Any unauthorized duplication, alteration, or falsification is strictly prohibited and subject to legal prosecution under the laws of Nepal. &copy; {{ date('Y') }} KTM-WDC. All rights reserved.
    </div>
</body>
</html>
