@php
    $logoPath = public_path('images/logo.png');
    $logoData = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Fee Invoice #{{ $fee->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #2d2d2d;
            background: #fff;
        }

        .invoice-wrapper {
            padding: 30px 40px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #4e73df;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header-top {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .institute-name {
            font-size: 22px;
            font-weight: bold;
            color: #4e73df;
            letter-spacing: 1px;
        }

        .institute-sub {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .institute-address {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
            line-height: 1.4;
        }

        .rupee {
            font-family: DejaVu Sans !important;
            font-weight: normal !important;
        }

        .invoice-label {
            font-size: 28px;
            font-weight: bold;
            color: #2d2d2d;
            letter-spacing: 2px;
        }

        .invoice-id {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background: #1cc88a;
            color: #fff;
        }

        /* Info Boxes */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 22px;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .info-box:last-child {
            padding-right: 0;
            padding-left: 20px;
        }

        .info-box-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 3px 0;
            font-size: 12px;
        }

        .info-box td:first-child {
            color: #888;
            width: 45%;
        }

        .info-box td:last-child {
            color: #2d2d2d;
            font-weight: 600;
        }

        /* Fee Summary */
        .summary-box {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 22px;
        }

        .summary-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .summary-label {
            display: table-cell;
            color: #666;
            width: 60%;
        }

        .summary-value {
            display: table-cell;
            font-weight: 600;
            text-align: right;
        }

        .summary-total {
            border-top: 2px solid #4e73df;
            padding-top: 8px;
            margin-top: 8px;
        }

        .summary-total .summary-label {
            color: #4e73df;
            font-weight: bold;
            font-size: 14px;
        }

        .summary-total .summary-value {
            color: #4e73df;
            font-weight: bold;
            font-size: 14px;
        }

        /* Payments Table */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .payments-table thead tr {
            background: #4e73df;
            color: #fff;
        }

        .payments-table thead th {
            padding: 9px 12px;
            font-size: 11px;
            font-weight: 600;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payments-table tbody tr {
            border-bottom: 1px solid #e3e6f0;
        }

        .payments-table tbody tr:nth-child(even) {
            background: #f8f9fc;
        }

        .payments-table tbody td {
            padding: 9px 12px;
            font-size: 12px;
        }

        .method-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            background: #e8efff;
            color: #4e73df;
        }

        /* Footer */
        .footer {
            border-top: 2px solid #e3e6f0;
            padding-top: 14px;
            margin-top: 10px;
            text-align: center;
            color: #aaa;
            font-size: 10px;
        }

        .footer-note {
            margin-bottom: 4px;
        }

        /* Paid stamp */
        .paid-stamp {
            position: absolute;
            top: 40px;
            right: 40px;
            border: 4px solid #1cc88a;
            color: #1cc88a;
            font-size: 32px;
            font-weight: bold;
            padding: 6px 16px;
            border-radius: 8px;
            opacity: 0.25;
            transform: rotate(-15deg);
            letter-spacing: 4px;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            margin-top: -200px;
            margin-left: -200px;
            opacity: 0.05;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper" style="position:relative;">

        {{-- Watermark logo --}}
        <div class="watermark">
            @if(!empty($logoData))
                <img src="data:image/png;base64,{{ $logoData }}" alt="Watermark Logo" />
            @endif
        </div>

        {{-- PAID watermark stamp --}}
        <div class="paid-stamp">PAID</div>

        {{-- HEADER --}}
        <div class="header">
            <div class="header-top">
                <div class="header-left">
                    <div class="institute-name">FOMS ACADEMY</div>
                    <div class="institute-address">
                        Vazhakkala,<br>
                        Kakkanad,<br>
                        Kochi - 682021
                    </div>
                </div>
                <div class="header-right">
                    @if(!empty($logoData))
                        <img src="data:image/png;base64,{{ $logoData }}" style="max-height: 70px; width: auto;" alt="Logo" />
                    @endif
                </div>
            </div>
        </div>

        {{-- INFO --}}
        <div class="info-row">

            <div class="info-box">
                <div class="info-box-title">Student Details</div>
                <table>
                    <tr>
                        <td>Name</td>
                        <td>{{ $fee->student->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Admission No.</td>
                        <td>{{ $fee->student->admission_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Contact</td>
                        <td>
                            {{ $fee->student->formatted_contact_number ?? '-' }}
                            @if($fee->student->is_whatsapp_different)
                                <br><small style="color: green;">WhatsApp: {{ $fee->student->formatted_whatsapp_number }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Class</td>
                        <td>{{ $fee->classRoom->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <div class="info-box-title">Invoice Details</div>
                <table>
                    <tr>
                        <td>Receipt No.</td>
                        <td>{{ $fee->receipt_no ?? 'REC-' . str_pad($fee->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td>Fee Type</td>
                        <td>{{ ucfirst($fee->type) }}</td>
                    </tr>
                    <tr>
                        <td>Due Date</td>
                        <td>{{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        @php
                            $lastPaymentDate = $fee->payments()->max('paid_date');
                        @endphp
                        @if($lastPaymentDate)
                            <td>Receipted On</td>
                            <td>{{ \Carbon\Carbon::parse($lastPaymentDate)->format('d M Y') }}</td>
                        @endif
                    </tr>
                </table>
            </div>

        </div>

        {{-- FEE SUMMARY --}}
        @php
            $totalPaid = $fee->payments->sum('paid_amount');
            $remaining = $fee->amount - $totalPaid;
        @endphp

        <div class="summary-box">
            <div class="summary-title">Fee Summary</div>

            <div class="summary-row">
                <div class="summary-label">Total Fee Amount</div>
                <div class="summary-value"><span class="rupee">&#8377;</span> {{ number_format($fee->amount, 2) }}</div>
            </div>

            <div class="summary-row">
                <div class="summary-label">Amount Paid</div>
                <div class="summary-value" style="color:#1cc88a;"><span class="rupee">&#8377;</span> {{ number_format($totalPaid, 2) }}</div>
            </div>

            <div class="summary-row summary-total">
                <div class="summary-label">Balance</div>
                <div class="summary-value"><span class="rupee">&#8377;</span> {{ number_format(max(0, $remaining), 2) }}</div>
            </div>
        </div>

        {{-- PAYMENT HISTORY --}}
        <div class="section-title">Payment History</div>

        <table class="payments-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment Date</th>
                    <th>Amount Paid</th>
                    <th>Method</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fee->payments as $i => $payment)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($payment->paid_date)->format('d M Y') }}</td>
                        <td><strong><span class="rupee">&#8377;</span> {{ number_format($payment->paid_amount, 2) }}</strong></td>
                        <td>
                            <span class="method-badge">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#aaa;">No payment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-note" style="color: #c9302c; font-weight: bold; margin-bottom: 6px; font-size: 11px;">
                Admission Fee once paid is strictly non-refundable under any circumstances.
            </div>
            <div class="footer-note">This is a system-generated Receipt. No signature required.</div>
            <div>FOMS ACADEMY &mdash; PDF Generated on {{ now()->format('d M Y, h:i A') }}</div>
        </div>

    </div>
</body>

</html>
