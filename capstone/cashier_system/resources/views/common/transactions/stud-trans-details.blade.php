@extends('layout.main-master')
@section('content')

<div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <main class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="bg-light border" style="padding:2%">
            <h3><strong>Student Transaction Details</strong></h3>
            @if ($TransactionDetails->isNotEmpty())
            <div>
                <table class="table">
                    <tr>
                        <td>
                            <p><strong>Transaction ID:</strong></p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->id }}</p>
                        </td>
                        <td>
                            <p><strong>Transaction Date:</strong></p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->transaction_date }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="table">
                    <tr>
                        <td>
                            <p> {{ $TransactionDetails[0]->first_name }}</p>
                        </td>
                        <td>
                            <p> {{ $TransactionDetails[0]->middle_name }}</p>
                        </td>
                        <td>
                            <p> {{ $TransactionDetails[0]->last_name }}</p>
                        </td>
                        <td>
                            <p> {{ $TransactionDetails[0]->suffix }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($TransactionDetails as $fees)
                    <tr>
                        <td>{{ $fees->fee_id }}</td>
                        <td>{{ $fees->fee_name }}</td>
                        <td>{{ $fees->amount }}</td>
                        <td>{{ $fees->quantity }}</td>
                        <td>{{ $fees->subtotal }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="table">
                <td><p><strong>Amount Paid:</strong> {{ $TransactionDetails[0]->amount_paid }}</p></td>
                <td><p><strong>Balance Due:</strong> {{ $TransactionDetails[0]->balance_due }}</p></td>
                <td><p><strong>Total Amount:</strong> {{ $TransactionDetails[0]->total_amount }}</p></td>
            </table>

            <div>
                <!-- Pay Now Button -->
                @if($TransactionDetails[0]->amount_paid == 0)
                    <a href="{{ route('student.transaction.pay', ['id' => $TransactionDetails[0]->id]) }}" class="btn btn-primary">
                        Pay Now
                    </a>
                @elseif($TransactionDetails[0]->balance_due == 0)
                    <a class="btn btn-secondary" disabled>
                        Paid in Full
                    </a>
                @endif

                @if(!$receipt) 
                    <a href="{{ route('student.generate.receipt', ['id' => $TransactionDetails[0]->id]) }}" class="btn btn-primary">
                        Generate Receipt
                    </a>
                @else
                    <a href="{{ route('student.receipt.details', ['id'=>$receipt->id]) }}" class="btn btn-danger" onclick="printTransaction()">
                        Print Receipt
                    </a>
                @endif
            </div>

            @else
            <p>No details found for this transaction.</p>
            @endif
        </div>
    </main>
</div>

<script>
        function payTransaction(transactionId) {
        if (confirm("Are you sure you want to mark this transaction as paid?")) {
            fetch("{{ route('student.transaction.pay') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    transaction_id: transactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Transaction has been marked as paid.");
                    location.reload(); // Refresh page to update status
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    }

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