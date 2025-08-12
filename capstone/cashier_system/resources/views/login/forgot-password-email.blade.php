@extends('layout.main-user')

@section('content')
<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Forgot Password</h1>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('forgot.password.email') }}">
                        @csrf
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <button class="btn btn-primary mt-3">Send OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
