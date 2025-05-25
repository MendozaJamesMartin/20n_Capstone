@if (session('success') || session('error'))
<div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div class="toast align-items-center text-white {{ session('success') ? 'bg-success' : 'bg-danger' }} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('success') ?? session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.querySelector('.toast');
        if (toastEl) {
            const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
            bsToast.show();
        }
    });
</script>
@endif
