@extends('layout.main-master')
@section('content')

    <div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
        <main class="container" style="width:50%;"> 

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-light border" style="padding:2%">
                <h3><strong>Concessionaire Transaction Details</strong></h3>
                @if ($TransactionDetails->isNotEmpty())
                    <div>
                        <table class="table">
                            <tr>
                                <td><p><strong>Transaction ID:</strong></p></td>
                                <td><p>{{ $TransactionDetails[0]->id }}</p></td>
                                <td><p><strong>Transaction Date:</strong></p></td>
                                <td><p>{{ $TransactionDetails[0]->transaction_date }}</p></td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table class="table">
                            <tr>
                                <td><p><strong> Concessionaire Name: </strong></p></td>
                                <td><p> {{ $TransactionDetails[0]->name }} </p></td>
                            </tr>
                        </table>
                    </div>
                    <br>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utility Type</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($TransactionDetails as $bills)
                                <tr>
                                    <td>{{ $bills->bill_id }}</td>
                                    <td>{{ $bills->utility_type }}</td>
                                    <td>{{ $bills->amount_paid }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="table">
                        <p><strong>Total Amount:</strong> {{ $TransactionDetails[0]->total_amount }}</p>
                    </table>

                    <div>
                        <a href="{{ route('concessionaire.receipt.details', ['id'=>$receipt->id]) }}" class="btn btn-danger" onclick="printTransaction()">
                            Print Receipt
                        </a>
                    </div>

                @else
                    <p>No details found for this transaction.</p>
                @endif
            </div>
        </main>
    </div>

<script>
    function printTransaction() {
        // Open a new window for printing
        const printWindow = window.open('', '_blank');
        const printContent = document.querySelector('.bg-light').innerHTML;

        // Set up the content of the print window
        printWindow.document.open();
        printWindow.document.write(`
                <html>
                    <head>
                        <title>Transaction Receipt</title>
                        <style>
                            /* General styles for printed content */
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                line-height: 1.6;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                            }
                            table, th, td {
                                border: 1px solid black;
                            }
                            th, td {
                                padding: 10px;
                                text-align: left;
                            }
                            .title {
                                text-align: center;
                                font-size: 24px;
                                font-weight: bold;
                                margin-bottom: 20px;
                            }
                            .no-print {
                                display: none !important;
                            }

                            /* Watermark */
                            .watermark {
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                font-size: 5em;
                                color: rgba(0, 0, 0, 0.1);
                                pointer-events: none;
                                z-index: -1;
                            }

                            /* Print-specific styles */
                            @media print {
                                body {
                                    color: #000;
                                    background: none;
                                    font-size: 12px;
                                }
                                table {
                                    font-size: 12px;
                                }
                                .no-print {
                                    display: none;
                                }
                                @page {
                                    margin: 1in; /* 1-inch margins */
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="title">Transaction Receipt</div>
                        <!-- Watermark Text -->
                        <div class="watermark">For Demonstration Purposes Only</div>
                        ${printContent}
                    </body>
                </html>
            `);
        printWindow.document.close();

        // Trigger the print dialog
        printWindow.print();

        // Close the print window after printing
        printWindow.onafterprint = () => {
            printWindow.close();
        };
    }
</script>

@endsection
