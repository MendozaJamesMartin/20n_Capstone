<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt</title>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 1;
            padding: 1;
            font-family: Arial, sans-serif;
            font-size: 14px;
            width: 99%;
            height: 99%;
            line-height: 0.86; /* keeps overall height same as 12px text */
        }

        table { width: 100%; border-collapse: collapse; }
        td, th {
            padding: 3px;
            vertical-align: middle;
            transform: translateY(-1px); /* centers growth visually */
        }

        /* Fixed height for item section */
        .item-table {
            table-layout: fixed;
            height: 190px;
            overflow: hidden;          /* prevents pushing down */
            position: relative;        /* isolates internal layout */
        }
        .item-table tbody {
            height: 190px;
            display: table-row-group;  /* keep valid table semantics */
        }
        .item-table td, .item-table th {
            height: 15px;
            overflow: hidden;
            word-wrap: break-word;
            line-height: 1.1;
            font-size: 14px;
            transform: translateY(-1px);
        }

        .item-table .cell-wrap { white-space: normal; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section { margin-bottom: 4px; }
        .signature-box { height: 40px; }

        h2 {
            font-size: 14px;
            margin: 4px 0;
            transform: translateY(-1px);
        }

        hr {
            margin: 2px 0;
            border: none;
            border-top: 1px solid #000;
        }

        .invisible-text { color: transparent; }

        /* Padding utilities */
        .ps-1 { padding-left: 4px !important; }
        .ps-2 { padding-left: 8px !important; }
        .ps-3 { padding-left: 16px !important; }
        .ps-4 { padding-left: 32px !important; }
        .ps-5 { padding-left: 64px !important; }
        .pe-1 { padding-right: 4px !important; }
        .pe-2 { padding-right: 8px !important; }
        .pe-3 { padding-right: 16px !important; }
        .pe-4 { padding-right: 32px !important; }
        .pe-5 { padding-right: 64px !important; }

        strong, div, span {
            transform: translateY(-1px);
        }

        /* Fine-tune: bring TOTAL and amount-in-words up one line */
        .align-up-1 {
            transform: translateY(-14px) !important; /* roughly one 14px line */
        }
    </style>
</head>
<body>

    {{-- Helper for wrapping long fee names --}}
    @php
        function wrapTextLines($text, $maxLength = 45) {
            return explode("\n", wordwrap($text, $maxLength, "\n", true));
        }
    @endphp

    {{-- Header notes --}}
    <table class="section invisible-text">
        <tr>
            <td>
                <strong>&nbsp; <br><br>
                    &nbsp;<br>
                </strong>
            </td>
        </tr>
    </table>

    {{-- Header --}}
    <table class="section invisible-text">
        <tr>    
            <td style="width: 25%;">&nbsp;</td>
            <td style="width: 50%;">
                <div class="text-center">
                    &nbsp; <br>
                    &nbsp;
                </div>
                <hr style="border: none;">
                <div class="text-center">
                    <h2>&nbsp;</h2>
                </div>
            </td>
            <td style="width: 25%;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 25%;">&nbsp;</td>
        </tr>
    </table>

    {{-- Date --}}
    <table class="section">
        <tr><td>&nbsp;</td></tr>
        <tr>
            <td style="width: 35%;" class="invisible-text">&nbsp;</td>
            <td style="width: 65%;" class="text-center ps-4">
                {{ \Carbon\Carbon::parse($TransactionDetails[0]->transaction_date ?? now())->format('m-d-Y') }}
            </td>
        </tr>
    </table>

    {{-- Institution and Customer --}}
    <table class="section" style="position: relative; height: 40px;">
        <tr class="text-center invisible-text">
            <td><strong>&nbsp;</strong></td>
        </tr>
        <tr style="position: absolute; top: 18px; left: 0; width: 100%;">
            <td class="text-center">
                <strong style="text-transform: uppercase;">
                    {{ $TransactionDetails[0]->customer_name ?? 'John Doe' }}
                </strong>
            </td>
        </tr>
    </table>

    {{-- Itemized Section --}}
    @php
        $maxRows = 14; // allows extra lines for wrapping
        $itemCount = 0;
        $grouped = $TransactionDetails->groupBy('fee_label');
    @endphp

    <table class="section item-table">
        <thead class="invisible-text">
            <tr>
                <th style="width: 60%;">&nbsp;</th>
                <th style="width: 20%;">&nbsp;</th>
                <th style="width: 20%;">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $label => $feesGroup)
                @php
                    $displayLabel = trim($label);
                    $hasLabel = $displayLabel !== '' && strtolower($displayLabel) !== 'none';
                @endphp

                {{-- Label Row --}}
                @if($hasLabel)
                    <tr>
                        <td colspan="3" class="cell-wrap ps-4"><strong>{{ $displayLabel }}</strong></td>
                    </tr>
                    @php $itemCount++; @endphp
                @endif

                @foreach($feesGroup as $fee)
                    @php
                        $displayName = $fee->fee_name;
                        if ($fee->quantity > 1) {
                            $displayName .= " ({$fee->quantity})";
                        }
                        $lines = wrapTextLines($displayName, 45);
                    @endphp

                    @foreach($lines as $i => $line)
                        <tr>
                            <td colspan="2" class="cell-wrap {{ $hasLabel ? ($i == 0 ? 'ps-5' : 'ps-5 ps-1') : ($i == 0 ? 'ps-4' : 'ps-4 ps-1') }}">
                                {{ $i == 0 ? ($hasLabel ? '- ' . $line : $line) : $line }}
                            </td>
                            @if($i == 0)
                                <td class="text-center pe-3">{{ number_format($fee->subtotal, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                        @php
                            // ✅ increment for every printed line
                            $itemCount++;
                        @endphp
                    @endforeach
                @endforeach
            @endforeach

            {{-- Blank filler rows --}}
            @for ($i = $itemCount; $i < $maxRows; $i++)
                <tr><td>&nbsp;</td></tr>
            @endfor

            {{-- Static Total --}}
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td colspan="2" class="text-center invisible-text"><strong>&nbsp;</strong></td>
                <td class="text-center pe-3"><strong>{{ number_format($TransactionDetails->sum('subtotal'), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Amount in Words --}}
    <table class="section">
        <td colspan="2"></td>
        <td class="pe-4 text-right"><strong>{{ $amountInWords }}</strong></td>
    </table>

    <table class="section"><tr><td>&nbsp;</td></tr></table>

    {{-- Payment Method --}}
    <table class="section invisible-text">
        <tr>
            <td style="width: 25%;">&nbsp;</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
    </table>

    {{-- Signature --}}
    <!--
    <table class="section" style="position: relative; height: 50px;">
        <tr>
            <td class="invisible-text">
                <div class="text-left">&nbsp;</div>
                <div class="text-left">&nbsp;</div>
            </td>
        </tr>
        <tr style="position: absolute; bottom: 0; right: 0; width: 100%;">
            <td class="text-center ps-5" style="font-size: 12px;">
                <strong>
                    {{ $Cashier->first_name }}
                    {{ $Cashier->middle_name ? strtoupper(substr(trim($Cashier->middle_name), 0, 1)) . '.' : '' }}
                    {{ $Cashier->last_name }}
                    {{ $Cashier->suffix ? ' ' . $Cashier->suffix : '' }}
                </strong>
            </td>
        </tr>
    </table>
    -->

</body>
</html>
