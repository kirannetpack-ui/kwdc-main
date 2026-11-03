@extends('legal.layout')

@section('title', 'Terms of Service & Logistics Agreement')
@section('badge', 'Legal Framework')
@section('heading', 'KTM-WDC Terms of Service')

@section('content')
    <div class="callout">
        <strong>Binding Legal Contract:</strong> These Terms of Service constitute a legally enforceable agreement governing all logistics bookings, warehousing bailments, dispatch requests, equipment rentals, and transport services arranged via the KTM-WDC platform.
    </div>

    <h2>1. Acceptance of Terms & Governance</h2>
    <p>
        By registering an account, submitting a storage reservation, scheduling a freight dispatch, or accepting an assignment as a carrier or facility owner, you agree to be bound by these Terms, governed under the <strong>Muluki Civil Code (2074) of Nepal</strong>, the <strong>Electronic Transactions Act (2063)</strong>, and commercial transport regulations.
    </p>

    <h2>2. Platform Role & Nature of Services</h2>
    <p>
        KTM-WDC acts as a centralized digital logistics coordination network connecting enterprise shippers ("Clients"), verified storage facility operators ("Property Owners"), vetted commercial freight operators ("Drivers"), equipment partners, and licensed security agencies.
    </p>

    <h2>3. Warehouse Storage & Bailment Terms (Muluki Civil Code 2074)</h2>
    <p>
        Storage of goods within registered facilities constitutes a legal bailment relationship:
    </p>
    <ul>
        <li><strong>Standard of Care:</strong> Property Owners must exercise standard commercial diligence, including maintaining working fire extinguishers, perimeter security, operational CCTV monitoring, and weather-tight storage conditions.</li>
        <li><strong>Inspection of Capacity:</strong> Clients retain the right to inspect warehouse facilities or review certified inspection certificates prior to executing lease commitments.</li>
        <li><strong>Demurrage & Unclaimed Cargo:</strong> Goods remaining uncollected after the expiration of paid storage tenures shall incur demurrage charges at statutory standard rates. Goods unclaimed after 60 days following written notice may be liquidated pursuant to Chapter 11 (Bailment) of the Muluki Civil Code.</li>
    </ul>

    <h2>4. Carrier Carriage Liability & Limitations</h2>
    <p>
        To preserve affordable freight transportation rates across Nepal, carriage liability is strictly demarcated:
    </p>
    <ul>
        <li><strong>Statutory Liability Cap:</strong> In the absence of a declared commercial invoice insurance endorsement, the carrier and KTM-WDC's aggregate liability for damage, destruction, or physical loss of cargo in transit is limited to <strong>NPR 250 per kilogram</strong> of actual lost weight or the total freight charges paid for that specific consignment, whichever is lower.</li>
        <li><strong>Declared Value Cargo:</strong> High-value consignments (electronics, pharmaceuticals, luxury merchandise) exceeding standard liability caps must be declared upon dispatch booking and covered by comprehensive transit insurance underwriters.</li>
        <li><strong>Exclusions from Carrier Liability:</strong> Neither KTM-WDC nor assigned drivers shall be liable for losses caused by: (a) acts of God, extreme weather, floods, or landslides across highway corridors; (b) inherent vice or improper manufacturer packaging; (c) civil unrest, strikes (bandhs), or government highway blockades; or (d) shipper misdeclaration.</li>
    </ul>

    <h2>5. Absolute Prohibition on Hazardous Cargo & Contraband</h2>
    <p>
        Shippers are strictly prohibited from depositing, packaging, or dispatching:
    </p>
    <ul>
        <li>Explosives, firearms, ammunition, fireworks, or radioactive isotopes.</li>
        <li>Unmanifested narcotics, illegal pharmaceutical compounds, or wildlife derivatives.</li>
        <li>Unpackaged flammable solvents, toxic corrosives, or biological hazards without written prior authorization and HazMat certification.</li>
        <li>Unlawful currency, counterfeit goods, or smuggled commodities evading customs duties.</li>
    </ul>
    <p style="color: #f87171; font-weight: 600;">
        Violation of this section results in immediate platform termination, forfeiture of security deposits, and immediate handover of materials to Nepal Police and the Department of Revenue Investigation.
    </p>

    <h2>6. Payments, Tariffs & Billing</h2>
    <p>
        Rates for warehousing (per sq ft/month), dispatches (base rate + per-kilometer transit rate), and equipment rentals are calculated transparently and billed in Nepalese Rupees (NPR). All applicable taxes, including Value Added Tax (VAT) and statutory municipal levies, are itemized on official tax invoices.
    </p>

    <h2>7. Equipment Rental & Operating Safety</h2>
    <p>
        Forklifts, pallet jacks, and heavy machinery leased via the Equipment Portal must be operated solely by certified operators. Lessees are strictly liable for equipment damages resulting from negligence or unauthorized mechanical alterations.
    </p>

    <h2>8. Dispute Resolution & Exclusive Jurisdiction</h2>
    <p>
        Any controversy, claim, or dispute arising out of or relating to services rendered through KTM-WDC shall first be submitted to good-faith mediation within 30 days. If unresolved, all legal proceedings shall fall under the <strong>exclusive jurisdiction of the competent courts of Kathmandu District, Nepal</strong>.
    </p>
@endsection
