<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 1;
            padding: 1;
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 99%;
            height: 99%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 3px;
            vertical-align: top;
        }

        .item-table {
            table-layout: fixed;
            height: 190px;
        }

        .item-table td, .item-table th {
            height: 15px;
            overflow: hidden;
            word-wrap: break-word;
            line-height: 1.1;
            font-size: 12px;
        }

        .item-table .cell-wrap {
            white-space: normal;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .section {
            margin-bottom: 4px;
        }

        .signature-box {
            height: 40px;
        }

        h2 {
            font-size: 12px;
            margin: 4px 0;
        }

        hr {
            margin: 2px 0;
            border: none;
            border-top: 1px solid #000;
        }

        .invisible-text {
            color: transparent;

        /* padding-start (left) utilities similar to bootstrap ps-* */
        .ps-1 { padding-left: 4px !important; }
        .ps-2 { padding-left: 8px !important; }
        .ps-3 { padding-left: 16px !important; }
        .ps-4 { padding-left: 32px !important; }
        .ps-5 { padding-left: 64px !important; }

        /* padding-end (right) utilities similar to bootstrap pe-* */
        .pe-1 { padding-right: 4px !important; }
        .pe-2 { padding-right: 8px !important; }
        .pe-3 { padding-right: 16px !important; }
        .pe-4 { padding-right: 32px !important; }
        .pe-5 { padding-right: 64px !important; }
        }
    </style>
</head>
<body>

    {{-- Header notes --}}
    <table class="section invisible-text">
        <tr>
            <td>
                <strong>ACCOUNTABLE FORM N. 51-C <br><br>
                    Revised January, 1992 (ORIGINAL)<br>
                </strong>
            </td>
        </tr>
    </table>

    {{-- Header: 3-column --}}
    <table class="section invisible-text">
        <tr>    
            <td style="width: 25%;">&nbsp;</td>
            <td style="width: 50%;">
                <div class="text-center">
                    Official Receipt of the <br>
                    Republic of the Philippines
                </div>
                <hr style="border: none;">
                <div class="text-center">
                    <h2>No. {{ $TransactionDetails[0]->receipt_number }}</h2>
                </div>
            </td>
            <td style="width: 25%;">&nbsp;</td>
        </tr>
    </table>

    {{-- Date --}}
    <table class="section">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 35%;" class="invisible-text">Fund</td>
            <td style="width: 65%;" class="text-right">{{ \Carbon\Carbon::parse($TransactionDetails[0]->transaction_date ?? now())->format('m-d-Y') }}</td>
        </tr>
    </table>

    {{-- Institution and Customer Name --}}
    <table class="section">
        <tr class="text-center invisible-text">
            <td><strong>POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</strong></td>
        </tr>
        <tr>
            <td class="text-center"><strong style="text-transform: uppercase;">{{ $TransactionDetails[0]->customer_name ?? 'John Doe' }}</strong></td>
        </tr>
    </table>

    {{-- Itemized Section --}}
    @php
        $maxRows = 12;
        $itemCount = count($TransactionDetails);
        $blankRows = $maxRows - $itemCount;
    @endphp

    <table class="section item-table">
        <thead class="invisible-text">
            <tr>
                <th style="width: 60%;">Nature of Collection</th>
                <th style="width: 20%;">Account Code</th>
                <th style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>

            @php
                // Group fees by fee_label
                $grouped = $TransactionDetails->groupBy('fee_label');
            @endphp

            @foreach($grouped as $label => $feesGroup)
                <!-- Label Row -->
                <tr>
                    <td colspan="3" class="cell-wrap ps-4"><strong>{{ $label }}</strong></td>
                </tr>

                <!-- Fee Rows under this label -->
                @foreach($feesGroup as $fee)
                    @php
                        $displayName = $fee->fee_name;
                        if ($fee->quantity > 1) {
                            $displayName .= " ({$fee->quantity})";
                        }
                    @endphp
                    <tr>
                        <td colspan="2" class="cell-wrap ps-5">- {{ $displayName }}</td>
                        <td class="text-center">{{ number_format($fee->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach

            <!-- Blank rows filler if needed -->
            @for ($i = 0; $i < $blankRows; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" class="text-center invisible-text"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ number_format($TransactionDetails->sum('subtotal'), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Amount in Words --}}
    <table class="section">
            <td colspan="2"></td>
            <td class="pe-4 text-right">
                <strong>{{ $amountInWords }}</strong>
            </td>
    </table>

    <table class="section">
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{-- Payment Method --}}
    <table class="section invisible-text">
        <tr>
            <td style="width: 25%;">Cash</td>
            <td style="width: 25%;" class="text-center">Drawee Bank</td>
            <td style="width: 25%;" class="text-center">Number</td>
            <td style="width: 25%;" class="text-center">Date</td>
        </tr>
        <tr>
            <td>Check</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Money Order</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{-- Signature --}}
    <table class="section">
        <tr>
            <td colspan="2" style="border-bottom: 0px;" class="invisible-text">
                <div class="text-left">Received the amount stated above.</div>
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="text-center">
                <strong>
                    {{ $Cashier->first_name }}
                    {{ $Cashier->middle_name ? strtoupper(substr(trim($Cashier->middle_name), 0, 1)) . '.' : '' }}
                    {{ $Cashier->last_name }}
                    {{ $Cashier->suffix ? ' ' . $Cashier->suffix : '' }}
                </strong>
                <br>
                <span style="visibility: hidden;">Collecting Officer</span>
            </td>
        </tr>
    </table>

</body>
</html>
