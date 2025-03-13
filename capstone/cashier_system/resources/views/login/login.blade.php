<html>
    <head>

    </head>

    <body style="background-image:url(bgpup5.jpg); background-position: center; background-repeat:no-repeat; background-size:cover;">
        @extends('layout.main-master')
        @section('content')
        <main class="container-fluid">
            <div class="row" style="height: auto;">
                <div class="col-lg-4 bg-secondary">
                    <div style="margin-top: 50%; margin-bottom:50%; margin-left:1%; margin-right:1%;">
                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class = "alert alert-danger">{{$error}}</div>
                        @endforeach
                    @endif

                    <form method="POST" action = "{{route('login.submit')}}">
                    @csrf
                    <div class="mb-4">
                        <label for="username" class="form-label">Username/Email</label>
                        <input type="text" class="form-control" id="username" name = "username" />
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name = "password"/>
                    </div>
                    <div class="mb-4">
                        <input type="checkbox" class="form-check-input" id="remember" name = "remember" />
                        <label for="remember" class="form-label">Remember Me</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary text-light main-bg">Login</button>
                    </div>
                    </form>
                    </div>
                </div>
                <div class="col-lg-8 col-md-0">
                </div>
            </div>
        </main>
        @endsection
    </body>
</html>
