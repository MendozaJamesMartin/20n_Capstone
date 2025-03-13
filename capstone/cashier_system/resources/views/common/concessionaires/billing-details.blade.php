@extends('layout.main-master')
@section('content')

    <div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
        <main class="container" style="width:50%;"> 

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-light border" style="padding:2%">
                <h3><strong>Billing Details</strong></h3>

                    <div>
                        <table class="table">
                            <tr>
                                <td><p><strong>Bill ID:</strong></p></td>
                                <td><p>1</p></td>
                                <td><p><strong>Due Date:</strong></p></td>
                                <td><p>lorem ipsum</p></td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table class="table">
                            <tr><p><strong>Concessionaire Information</strong></p></tr>
                            <tr>
                                <td><p>ID:</p></td>
                                <td><p>1</p></td>
                                <td><p>Concessionaire: </p></td>
                                <td><p>Concessionaire Name</p></td>
                            </tr>
                        </table>
                    </div>
                    <br>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utility</th>
                                <th>Amount</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>

                                <tr>
                                    <td>1</td>
                                    <td>Electricity</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>

                        </tbody>
                    </table>

                    <table class="table">
                        <p><strong>Total Amount:</strong> 1,000 </p>
                    </table>


            </div>
        </main>
    </div>

@endsection
