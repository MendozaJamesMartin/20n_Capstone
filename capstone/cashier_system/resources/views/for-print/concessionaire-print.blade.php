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
            font-size: 9px;
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
        }
    </style>
</head>
<body>

    {{-- Header notes --}}
    <table class="section invisible-text">
        <tr>
            <td>
                <strong>ACCOUNTABLE FORM N. 51-C <br>
                    <br>
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
                <br>
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
            <td style="width: 35%;" class="invisible-text">Fund</td>
            <td style="width: 65%;" class="text-right">{{ \Carbon\Carbon::parse($TransactionDetails[0]->transaction_date ?? now())->format('Y-m-d') }}</td>
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
        $maxRows = 13;
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
            @foreach($TransactionDetails as $bill)
                <tr>
                    <td style="border-top: 0px; border-bottom: 0px;">{{ $bill->bill_type }}</td>
                    <td style="border-top: 0px; border-bottom: 0px;"></td>
                    <td class="text-center" style="border-top: 0px; border-bottom: 0px;">{{ number_format($bill->amount_paid, 2) }}</td>
                </tr>
            @endforeach

            @for ($i = 0; $i < $blankRows; $i++)
                <tr>
                    <td style="border-top: 0px; border-bottom: 0px;">&nbsp;</td>
                    <td style="border-top: 0px; border-bottom: 0px;">&nbsp;</td>
                    <td style="border-top: 0px; border-bottom: 0px;">&nbsp;</td>
                </tr>
            @endfor

            <tr>
                <td colspan="2" class="text-center invisible-text"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ number_format($TransactionDetails->sum('amount_paid'), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Total --}}
    <table class="section">
        <tr>
            <td class="invisible-text">
                Amount in Words
            </td>
        </tr>
    </table>

    <table class="section">
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{-- Payment Method --}}
    <table class="section invisible-text">
        <tr>
            <td style="width: 25%; border-bottom: 0px;">Cash</td>
            <td style="width: 25%;" class="text-center">Drawee Bank</td>
            <td style="width: 25%;" class="text-center">Number</td>
            <td style="width: 25%;" class="text-center">Date</td>
        </tr>
        <tr>
            <td style="border-top: 0px; border-bottom: 0px;">Check</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="border-top: 0px;">Money Order</td>
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
            <td style="border-top: 0px; border-right: 0px;" ></td>
            <td style="border-top: 0px; border-left: 0px;" class="text-center">
                <strong>{{ $Cashier->first_name }} {{ $Cashier->last_name }}</strong>
                <br>
                <span style="visibility: hidden;">Collecting Officer</span>
            </td>
        </tr>
    </table>

    {{-- Notes --}}
    <table class="section invisible-text">
        <tr>
            <td>
                NOTE: Write the number and date of this receipt on the back of check/money<br>
                order when received.
            </td>
        </tr>
    </table>

    {{-- University Footer --}}
    <table>
        <tr>
            <td class="text-center invisible-text">
                <strong>"THE COUNTRY'S 1ST POLYTECHNIC U"</strong>
            </td>
        </tr>
    </table>

</body>
</html>
