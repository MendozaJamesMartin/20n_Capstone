@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%;">

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
                            <p>{{ $TransactionDetails[0]->transaction_id }}</p>
                        </td>
                        <td>
                            <p><strong>Transaction Date:</strong></p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->transaction_date }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            @if (!empty($TransactionDetails[0]->receipt_number))
                                <p><strong>Receipt Number:</strong> {{ $TransactionDetails[0]->receipt_number }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="table">
                    <tr>
                        <td>
                            <p> {{ $TransactionDetails[0]->customer_name }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p> {{ $TransactionDetails[0]->contact_info }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($TransactionDetails as $fees)
                    <tr>
                        <td>{{ $fees->fee_name }}</td>
                        <td>{{ $fees->fee_amount }}</td>
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

            <input type="hidden" name="receipt_number" id="receipt_number">

            @if (empty($TransactionDetails[0]->receipt_number))
                <button type="button" class="btn btn-primary mt-3" id="viewPrintReceiptBtn">
                    View and Print Receipt
                </button>
            @else
                <button type="button" class="btn btn-secondary mt-3" disabled>
                    Receipt Already Saved
                </button>
            @endif

            @else
            <p>No details found for this transaction.</p>
            @endif

            <!-- Modal for Receipt Number -->
            <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Save Printed Receipt Number</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label for="modal_receipt_number" class="form-label">Receipt Number</label>
                            <input type="text" class="form-control" id="modal_receipt_number" placeholder="Enter Receipt Number">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" id="confirmPaymentButton">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const transactionId = '{{ $TransactionDetails[0]->transaction_id }}';
    const finalizeUrl = '{{ route("finalize.transation", ["id" => $TransactionDetails[0]->transaction_id]) }}';
    const saveReceiptUrl = '{{ route("save.receipt") }}';
    const csrfToken = '{{ csrf_token() }}';

    const viewPrintBtn = document.getElementById('viewPrintReceiptBtn');
    const receiptModal = document.getElementById('receiptModal');
    const receiptInput = document.getElementById('modal_receipt_number');
    const confirmBtn = document.getElementById('confirmPaymentButton');

    viewPrintBtn?.addEventListener('click', function () {
        // Show modal first
        let modal = new bootstrap.Modal(receiptModal);
        modal.show();

        // After short delay, open PDF via POST in new tab
        setTimeout(() => {
            const pdfForm = document.createElement('form');
            pdfForm.method = 'POST';
            pdfForm.action = finalizeUrl;
            pdfForm.target = '_blank';
            pdfForm.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = csrfToken;
            pdfForm.appendChild(csrf);

            document.body.appendChild(pdfForm);
            pdfForm.submit();
        }, 300);
    });

    confirmBtn.addEventListener('click', function () {
        const receiptNumber = receiptInput.value.trim();

        if (!receiptNumber) {
            alert('Please enter a receipt number.');
            return;
        }

        fetch(saveReceiptUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                transaction_id: transactionId,
                receipt_number: receiptNumber
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to save receipt number');
            return response.json();
        })
        .then(data => {
            // Hide modal
            let modal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
            modal.hide();

            // Reload the page to reflect receipt number condition
            location.reload();
        })
        .catch(error => {
            alert(error.message || 'An error occurred while saving.');
        });
    });
</script>

@endsection