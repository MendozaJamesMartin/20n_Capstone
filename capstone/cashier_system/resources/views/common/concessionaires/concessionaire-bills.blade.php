@extends('layout.main-master')

@section('content')
<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Latest Concessionaire Bills</h2>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="billTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="electricity-tab" data-bs-toggle="tab" data-bs-target="#electricity" type="button" role="tab">Electricity</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="water-tab" data-bs-toggle="tab" data-bs-target="#water" type="button" role="tab">Water</button>
            </li>
        </ul>

        <div class="card shadow-sm p-3 mb-4 bg-light rounded tab-content" id="billTabsContent">
            <!-- Electricity Bills -->
            <div class="tab-pane fade show active" id="electricity" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Concessionaire</th>
                                <th>Billing Period</th>
                                <th>Bill Start Date</th>
                                <th>Bill End Date</th>
                                <th>kWh Used</th>
                                <th>₱/kWh</th>
                                <th>Current Charges</th>
                                <th>Previous Unpaid</th>
                                <th>Total Due</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($electricityBills as $bill)
                            <tr>
                                <td>{{ $bill->concessionaire_name }}</td>
                                <td>{{ $bill->billing_period }}</td>
                                <td>{{ $bill->bill_start_date }}</td>
                                <td>{{ $bill->bill_end_date }}</td>
                                <td>{{ number_format($bill->concessionaire_kwh_used, 2) }}</td>
                                <td>₱{{ number_format($bill->cost_per_kwh, 2) }}</td>
                                <td>₱{{ number_format($bill->concessionaire_total_amount, 2) }}</td>
                                <td>₱{{ number_format($bill->previous_unpaid, 2) }}</td>
                                <td>₱{{ number_format($bill->total_amount_due, 2) }}</td>
                                <td>{{ $bill->due_date }}</td>
                                <td><span class="badge bg-{{ $bill->status === 'Fully Paid' ? 'success' : ($bill->status === 'Partially Paid' ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                <td>
                                    <a href="{{ route('concessionaire.bill.electricity.pdf', ['id' => $bill->bill_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Billing Statement"><i class="fa-solid fa-receipt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center">No electricity bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Water Bills -->
            <div class="tab-pane fade" id="water" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Concessionaire</th>
                                <th>Billing Period</th>
                                <th>Current Charges</th>
                                <th>Previous Unpaid</th>
                                <th>Total Due</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waterBills as $bill)
                            <tr>
                                <td>{{ $bill->concessionaire_name }}</td>
                                <td>{{ $bill->billing_period }}</td>
                                <td>₱{{ number_format($bill->current_charges, 2) }}</td>
                                <td>₱{{ number_format($bill->previous_unpaid, 2) }}</td>
                                <td>₱{{ number_format($bill->total_amount_due, 2) }}</td>
                                <td>{{ $bill->due_date }}</td>
                                <td><span class="badge bg-{{ $bill->status === 'Fully Paid' ? 'success' : ($bill->status === 'Partially Paid' ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                <td>
                                    <a href="{{ route('concessionaire.bill.water.pdf', ['id' => $bill->bill_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Billing Statement"><i class="fa-solid fa-receipt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center">No water bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
