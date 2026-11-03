@extends('legal.layout')

@section('title', 'Privacy Policy & Data Governance')
@section('badge', 'Privacy & Data Protection')
@section('heading', 'KTM-WDC Privacy Policy')

@section('content')
    <div class="callout">
        <strong>Summary of Protection:</strong> KTM-WDC ("Warehouse & Distribution Connect") strictly safeguards your personal data, logistics consignment records, and vehicle telemetry. We operate under the <strong>Individual Privacy Act (2075) of Nepal</strong> and modern international data protection benchmarks.
    </div>

    <h2>1. Overview & Scope</h2>
    <p>
        This Privacy Policy outlines how KTM-WDC collects, utilizes, encrypts, and protects information gathered through our commercial logistics management portal, mobile driver applications, dispatch dispatchers, and warehousing partner portals. By utilizing KTM-WDC services, you acknowledge the data practices described herein.
    </p>

    <h2>2. Data We Collect</h2>
    <p>To provide automated freight dispatch, warehouse capacity booking, and fleet tracking, we process the following categories of data:</p>
    <ul>
        <li><strong>User & Enterprise Identity:</strong> Legal business name, authorized representative name, email address, phone number, corporate tax identification (PAN/VAT), and citizenship/license credentials for vetted drivers and guards.</li>
        <li><strong>Commercial Cargo & Consignment Data:</strong> Goods description, package weight, volumetric dimensions, storage requirements (ambient or cold storage), declared commercial invoice values, consignee names, and drop-off destinations.</li>
        <li><strong>Telematics & Live Vehicle GPS Coordinates:</strong> Real-time latitude and longitude coordinates transmitted by drivers during active dispatches, speed, route progression, stop durations, and geofence arrival milestones.</li>
        <li><strong>Financial & Transaction Records:</strong> Invoices, escrow deposit logs, fee calculations, payment receipts, and banking disbursement accounts.</li>
        <li><strong>Facility Security Telemetry:</strong> Warehouse CCTV status logs, visitor check-in logs, and authorized cargo release verifications.</li>
    </ul>

    <h2>3. Purpose & Legal Basis of Processing</h2>
    <p>We process operational and personal data solely for legitimate logistics facilitation:</p>
    <ol>
        <li>To execute contractually binding warehouse leases, cargo storage, and freight transit orders.</li>
        <li>To provide real-time GPS tracking and live ETA calculations to shippers and recipients across Nepal.</li>
        <li>To verify carrier licensing, cargo insurance eligibility, and vehicle roadworthiness under Department of Transport Management (DoTM) standards.</li>
        <li>To prevent cargo theft, insurance fraud, unauthorized inventory release, and breach of hazardous cargo bans.</li>
    </ol>

    <h2>4. Vehicle Telematics & GPS Tracking Protocols</h2>
    <p>
        GPS coordinates are monitored exclusively when a driver transitions an assigned dispatch or pickup job into <em>"In Transit"</em> status. Tracking automatically suspends upon verified cargo handover or order cancellation. Drivers maintain the right to inspect location tracking records associated with their vehicle fleet.
    </p>

    <h2>5. Information Sharing & Third-Party Disclosures</h2>
    <p>KTM-WDC maintains a strict zero-sale policy: <strong>we never sell or rent client, driver, or warehouse owner data to third-party advertisers.</strong> Disclosures are limited to:</p>
    <ul>
        <li><strong>Counterparty Operational Handshake:</strong> Providing consignee addresses and driver telephone numbers necessary to consummate physical pickup and delivery.</li>
        <li><strong>Authorized Insurance Underwriters:</strong> Submitting verified consignment manifests and loss-prevention telemetry in the event of an authorized claims investigation.</li>
        <li><strong>Regulatory & Law Enforcement Authorities:</strong> Compliance with valid court orders, subpoenas, or statutory reporting mandates under Nepal Police, Department of Revenue Investigation, or customs checkpoints.</li>
    </ul>

    <h2>6. Security Standards & Retention</h2>
    <p>
        All communication between clients, mobile endpoints, and the KTM-WDC cloud platform is encrypted using TLS 1.3 encryption. Passwords and sensitive authentication tokens are hashed using bcrypt. Consignment and GPS tracking histories are archived in accordance with statutory accounting retention requirements (7 years under Nepal financial governance).
    </p>

    <h2>7. Your Legal Rights & Data Inquiries</h2>
    <p>
        Under the Privacy Act of Nepal, you possess the right to review your registered profile data, request corrections of inaccurate information, export your transaction ledger, and request account deactivation.
    </p>
    <p>
        For inquiries or privacy complaints, contact our Data Protection Officer at:
        <br>
        <strong>KTM-WDC Legal & Compliance Division</strong><br>
        Email: <a href="mailto:privacy@kwdc.test" style="color: var(--accent-orange);">privacy@kwdc.test</a><br>
        Office: Baluwatar Freight Corridor, Kathmandu, Nepal &bull; Phone: +977-1-5912400
    </p>
@endsection
