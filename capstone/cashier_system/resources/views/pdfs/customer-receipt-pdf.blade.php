<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .section {
            margin-bottom: 8px;
        }

        .signature-box {
            height: 50px;
        }
    </style>
</head>
<body>

    {{-- Header notes --}}
    <table class="section">
        <tr>
            <td>
                <strong>ACCOUNTABLE FORM N. 51-C <br>
                    Revised January, 1992 (ORIGINAL)<br>
                    </strong>
            </td>
        </tr>
    </table>

    {{-- Header: 3-column --}}
    <table class="section">
        <tr>    
            <td style="width: 30%;">&nbsp;</td>
            <td style="width: 40%;">
                <div class="text-center">
                    Official Receipt of the <br>
                    Republic of the Philippines<br>
                </div>
                <div>
                    <hr>
                    <h2>No. {{ $TransactionDetails[0]->receipt_number }}</h2>
                </div>
            </td>
            <td style="width: 30%;">&nbsp;</td>
        </tr>
    </table>

    {{-- Date --}}
    <table class="section">
        <tr>
            <td style="width: 33.33%;">Fund</td>
            <td style="width: 67.67%;">Date: {{ $TransactionDetails[0]->transaction_date ?? now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    {{-- Institution and Customer Name --}}
    <table class="section">
        <tr class="text-center">
            <td><strong>POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</strong></td>
        </tr>
        <tr>
            <td>Payor <strong style="text-transform: uppercase;">{{ $TransactionDetails[0]->customer_name ?? 'John Doe' }}</strong></td>
        </tr>
    </table>

    {{-- Itemized Section --}}
    @php
        $maxRows = 10;
        $itemCount = count($TransactionDetails);
        $blankRows = $maxRows - $itemCount;
    @endphp

    <table class="section">
        <tr>
            <th style="width: 65%;">Nature of Collection</th>
            <th style="width: 15%;">Account Code</th>
            <th style="width: 20%;">Amount</th>
        </tr>

        @foreach($TransactionDetails as $fees)
            <tr>
                <td style="border-top: 0px; border-bottom: 0px;">{{ $fees->fee_name }}</td>
                <td style="border-top: 0px; border-bottom: 0px;"></td>
                <td style="border-top: 0px; border-bottom: 0px;" class="text-center">{{ number_format($fees->subtotal, 2) }}</td>
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
            <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
            <td class="text-center"><strong>{{ number_format($TransactionDetails->sum('subtotal'), 2) }}</strong></td>
        </tr>
    </table>

    {{-- Total --}}
    <table class="section">
        <tr>
            <td>
                Amount in Words
            </td>
        </tr>
    </table>

    {{-- Payment Method --}}
    <table class="section">
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
    <table>
        <tr>
            <td colspan="2" style="border-bottom: 0px;">
                <div class="text-left">Received the amount stated above.</div>
            </td>
        </tr>
        <tr>
            <td style="border-top: 0px; border-right: 0px;" ></td>
            <td style="border-top: 0px; border-left: 0px;" class="text-center">
                <strong>{{ $Cashier->first_name }} {{ $Cashier->last_name }}</strong>
                <hr>
                Collecting Officer
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <table>
        <tr>
            <td>
                NOTE: Write the number and date of this receipt on the back of check/money<br>
                order when received.
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <table>
        <tr>
            <td class="text-center">
                <strong>"THE COUNTRY'S 1ST POLYTECHNIC U"</strong>
            </td>
        </tr>
    </table>

</body>
</html>
