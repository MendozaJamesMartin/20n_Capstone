@extends('layout.main')
@section('content')

<main style="background-image:url('/bgpup2.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    
    <div class="container" style="width:50%">

        <div class="bg-light" style="padding:5%">
            <h1>Fee Details</h1>
            <form action="{{ route('UpdateFees', $fees->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="fee_name" class="form-label">fee Name:</label>
                    <input type="text" class="form-control" name="fee_name" id="fee_name" placeholder="fee Name" value="{{ old('fee_name', $fees->fee_name) }}" required>
                    @error('fee_name') 
                        <p style="color: red;">{{ $message }}</p> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="amount">Item Price:</label>
                    <input type="text" class="form-control" name="amount" id="amount" placeholder="00.00" value="{{ old('amount', $fees->amount) }}" required>
                    @error('amount') 
                        <p style="color: red;">{{ $message }}</p> 
                    @enderror
                </div>

                <button class="btn btn-danger btn-lg" type="submit">Update Item</button>
            </form>
        </div>

    </div>

</main>

@endsection
