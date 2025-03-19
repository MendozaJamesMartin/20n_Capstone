@extends('layout.main-master')
@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Login</h1>
                </div>
                <div class="card-body">
                    @if(Session::has('success'))
                    <div class="alert alert-success" role="alert">
                        {{ Session::get('success') }}
                    </div>
                    @elseif (Session::has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ Session::get('error') }}
                    </div>
                    @endif
                    <form method="POST" action="#">
                        @csrf
                        <div class="mb-4">
                            <label for="username" class="form-label">Username/Email</label>
                            <input type="text" class="form-control" id="username" name="username" />
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" />
                        </div>
                        <div class="mb-4">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" />
                            <label for="remember" class="form-label">Remember Me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary text-light main-bg">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-0">
    </div>

    </div>
    </div>

</main>

@endsection