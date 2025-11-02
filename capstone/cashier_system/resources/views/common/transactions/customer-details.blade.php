@extends('layout.main-master')

@section('content')
<main class="py-5 px-3" style="min-height: 85vh; background-color: #f9f9f9;">
    <div class="container" style="max-width: 800px;">

        {{-- Success/Error Messages --}}
        @if(session('success'))
        <div class="alert alert-success mt-3" style="white-space: pre-line;">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Transaction Summary --}}
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Transaction Details</h4>
            </div>
            <div class="card-body">
                <p class="mb-3">Please review the transaction details before finalizing.</p>

                <ul class="list-group mb-4">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Transaction ID:</strong>
                        <span>{{ $TransactionDetails[0]->transaction_id }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Receipt Number:</strong>
                        <span>{{ $TransactionDetails[0]->receipt_number }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Customer Name:</strong>
                        <span class="text-uppercase">{{ $TransactionDetails[0]->customer_name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Total Amount:</strong>
                        <span>₱{{ number_format($TransactionDetails[0]->total_amount, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Status:</strong>
                        @if ($TransactionDetails[0]->payment_status == "Pending")
                        <span id="tx-status" class="badge bg-warning text-dark">Pending</span>
                        @elseif ($TransactionDetails[0]->payment_status == "Completed")
                        <span id="tx-status" class="badge bg-success text-white">Completed</span>
                        @elseif ($TransactionDetails[0]->payment_status == "Cancelled")
                        <span id="tx-status" class="badge bg-danger text-white">Cancelled</span>
                        @endif
                    </li>
                </ul>

                {{-- Transaction Line Items --}}
                <h5 class="mb-3">Items Paid For</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Fee Name</th>
                                <th>Label</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Amount</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($TransactionDetails as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->fee_name }}</td>
                                <td>{{ $item->fee_label ?: '-' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->fee_amount, 2) }}</td>
                                <td class="text-end">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">Total</td>
                                <td class="text-end">₱{{ number_format($TransactionDetails[0]->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Action Buttons --}}
                <div class="text-end">
                    @if (!$hasActiveBatch && $TransactionDetails[0]->payment_status == "Pending")
                    <div class="alert alert-danger">
                        🚫 Cannot finalize transaction. No receipt numbers available. Please load a new batch first.
                    </div>
                    @else
                    @if ($TransactionDetails[0]->payment_status == "Pending")
                    <form id="finalizeForm" method="POST" action="{{ route('payments.disapprove', ['id' => $TransactionDetails[0]->transaction_id, 'redirectTo' => 'customer.transaction.details']) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="undoBtn" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Undo Transaction
                        </button>
                    </form>
                    <form id="finalizeForm" method="POST" action="{{ route('finalize.transation', ['id' => $TransactionDetails[0]->transaction_id]) }}" style="display: inline;">
                        @csrf
                        <button type="submit" id="finalizeBtn" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Finalize Transaction
                        </button>
                    </form>
                    @elseif ($TransactionDetails[0]->payment_status == "Completed")
                    {{-- Print Receipt Button --}}
                    <a href="{{ route('customer.receipt.pdf', ['id' => $TransactionDetails[0]->transaction_id]) }}"
                        class="btn btn-primary"
                        id="printReceiptBtn"
                        title="Receipt"
                        target="_blank">
                        View and Print Receipt
                    </a>

                    {{-- Cancel Receipt Button --}}
                    @if ($TransactionDetails[0]->receipt_status == "Issued")
                    <form id="cancelReceipt" method="POST" action="{{ route('cancel.receipt', ['id' => $TransactionDetails[0]->transaction_id]) }}" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" id="cancelReceiptBtn" class="btn btn-danger">
                            Cancel Receipt
                        </button>
                    </form>
                    @endif
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const form = document.getElementById('finalizeForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!confirm("Are you sure you want to finalize this transaction?")) {
                e.preventDefault();
            }
        });
    }

    const printBtn = document.getElementById('printReceiptBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            setTimeout(() => location.reload(), 1000);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const printBtn = document.getElementById('printReceiptBtn');
        const receiptStatus = "{{ $TransactionDetails[0]->receipt_status ?? '' }}";
        if (printBtn && receiptStatus === 'Issued') {
            printBtn.addEventListener('click', function (event) {
                if (!confirm('⚠️ This receipt has already been issued. Are you sure you want to reprint it?')) {
                    event.preventDefault();
                }
            });
        }
    });
</script>
@endsection
