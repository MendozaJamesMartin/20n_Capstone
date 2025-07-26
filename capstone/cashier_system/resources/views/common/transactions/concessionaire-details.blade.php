@extends('layout.main-master')

@section('content')
<main class="py-5 px-3" style="min-height: 85vh; background-color: #f9f9f9;">
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
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
                        <strong>Customer Name:</strong>
                        <span>{{ $TransactionDetails[0]->concessionaire_name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Total Amount:</strong>
                        <span>₱{{ number_format($TransactionDetails[0]->total_amount, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Status:</strong>
                        @if ($TransactionDetails[0]->amount_paid == 0)
                            <span id="tx-status" class="badge bg-warning text-dark">Pending</span>
                        @else
                            <span id="tx-status" class="badge bg-success text-white">Complete</span>
                        @endif
                    </li>
                </ul>

                <div class="text-end">
                    @if (!$hasActiveBatch)
                        <div class="alert alert-danger">
                            🚫 Cannot finalize transaction. No receipt numbers available. Please load a new batch first.
                        </div>
                    @elseif ($TransactionDetails[0]->amount_paid == 0)
                        <form id="finalizeForm" method="POST" action="{{ route('finalize.transation', ['id' => $TransactionDetails[0]->transaction_id]) }}" target="_blank" style="display: inline;">
                            @csrf
                            <button type="submit" id="finalizeBtn" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Finalize and Print Receipt
                            </button>
                        </form>
                    @else
                        <a href="{{ route('concessionaire.receipt.pdf', ['id' => $TransactionDetails[0]->transaction_id]) }}"
                           target="_blank"
                           class="btn btn-outline-primary">
                            🖨 View Receipt
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const form = document.getElementById('finalizeForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const confirmFinalize = confirm("Are you sure you want to finalize this transaction?");
            if (!confirmFinalize) {
                e.preventDefault();
                return;
            }

            // Delay to allow the finalize request to go through before UI changes
            setTimeout(() => {
                // ✅ Update the status badge
                const statusBadge = document.getElementById('tx-status');
                statusBadge.textContent = 'Complete';
                statusBadge.classList.remove('bg-warning', 'text-dark');
                statusBadge.classList.add('bg-success', 'text-white');

                // ✅ Replace finalize button with receipt view link
                const finalizeDiv = form.parentElement;
                finalizeDiv.innerHTML = `
                    <a href="{{ route('concessionaire.receipt.pdf', ['id' => $TransactionDetails[0]->transaction_id]) }}"
                       target="_blank"
                       class="btn btn-outline-primary">
                        🖨 View Receipt
                    </a>
                `;
            }, 1000); // Adjust delay if needed
        });
    }
</script>

@endsection
