@extends('layout.main-user')
@section('content')

<main style="
    background-image:url('/bgpup3.jpg');
    background-repeat:no-repeat;
    background-size:cover;
    background-position:top;
    min-height: 85vh;
    padding: 5%;
    display:flex;
    justify-content:center;
    align-items:center;
">

    <div class="container" style="max-width: 450px;">
        <div class="shadow-lg rounded-4 p-4"
             style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.85);">

            <h2 class="fw-bold text-center mb-4" style="color:#8b0000;">
                Email Verification
            </h2>

            {{-- Flash messages --}}
            @if(Session::has('message'))
                <div class="alert alert-success rounded-3 text-center">
                    {{ Session::get('message') }}
                </div>
            @elseif(Session::has('error'))
                <div class="alert alert-danger rounded-3 text-center">
                    {{ Session::get('error') }}
                </div>
            @endif

            {{-- OTP FORM --}}
            <form method="POST" action="{{ route('otp.verify') }}">
                @csrf

                <label class="form-label fw-semibold">Enter 6-digit OTP</label>

                {{-- 6 BOX OTP --}}
                <div class="d-flex gap-2 justify-content-center mb-4">
                    @for($i = 1; $i <= 6; $i++)
                        <input type="text"
                               maxlength="1"
                               class="otp-box form-control text-center fw-bold"
                               style="width:45px; height:55px; font-size:1.5rem; border-radius:12px;"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               data-index="{{ $i }}">
                    @endfor
                </div>

                <input type="hidden" name="otp_code" id="otp_full">

                @error('otp_code')
                    <div class="text-danger small text-center">{{ $message }}</div>
                @enderror

                <button type="submit"
                        class="btn w-100 text-light fw-semibold rounded-3 py-2 shadow-sm mt-3"
                        style="background:#8b0000;">
                    Verify Email
                </button>
            </form>

            {{-- RESEND OTP --}}
            <form method="POST" action="{{ route('otp.resend') }}" class="text-center mt-3">
                @csrf
                <button type="submit"
                        class="btn btn-link fw-semibold"
                        style="color:#8b0000;">
                    Resend OTP
                </button>
            </form>

        </div>
    </div>

</main>

{{-- OTP SCRIPT --}}
<script>
document.querySelectorAll('.otp-box').forEach((box, index, boxes) => {
    box.addEventListener('input', () => {
        box.value = box.value.replace(/\D/g, '');

        if (box.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }

        document.getElementById('otp_full').value =
            Array.from(boxes).map(b => b.value).join('');
    });

    box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && index > 0) {
            boxes[index - 1].focus();
        }
    });
});
</script>

@endsection
