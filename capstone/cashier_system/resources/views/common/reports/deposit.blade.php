@extends('layout.main-master')

@section('content')

<main style="min-height: 85vh; padding: 3%; 
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);">

    <div class="container mt-4" style="width: 75%;">

        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success mt-3 shadow-sm" style="white-space: pre-line;">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger mt-3 shadow-sm">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Outer Card -->
        <div class="bg-white rounded-4 shadow p-4"
            style="border: 1px solid #e5e7eb; animation: fadeIn 0.4s ease;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-0">Deposits Management</h3>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addFeeModal">
                    <i class="bi bi-plus-circle"></i> Add Deposit
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control shadow-sm"
                    placeholder="Search fees..."
                    style="border-radius: 15px;">
            </div>

            <!-- Table Wrapper -->
            <div class="table-responsive shadow-sm rounded-3"
                style="border: 1px solid #e2e3e5; background: #ffffff;">

                <table class="table table-hover mb-0" id="feesTable">
                    <thead class="table-light">
                        <tr style="cursor: pointer;">
                            <th onclick="sortTable(0)">Deposit Date</th>
                            <th onclick="sortTable(0)">OR No.</th>
                            <th onclick="sortTable(1)">Account Number</th>
                            <th onclick="sortTable(2)">Amount</th>
                            <th style="cursor: default;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr class="align-middle">
                            <td>June 05, 2026</td>
                            <td>26-074</td>
                            <td>LBP Account 0682-1020-47</td>
                            <td class="fw-semibold">
                                ₱4,576.00
                            </td>
                            <td>

                                <button class="btn btn-sm btn-warning shadow-sm" data-bs-toggle="modal">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        </div>

                    </tbody>
                </table>
            </div>

        </div> <!-- Card end -->

</main>

<!-- Fade-in animation -->
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table-hover tbody tr:hover {
        background: #f3f7ff !important;
        transition: 0.2s;
    }

    .btn,
    .form-control {
        transition: 0.2s;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
    }

    .modal-content {
        border-radius: 15px;
    }
</style>

@endsection