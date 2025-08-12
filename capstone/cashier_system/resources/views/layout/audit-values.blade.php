@php
    $values = is_array($data) ? $data : json_decode($data, true);
@endphp

@forelse ($values ?? [] as $key => $value)
    @php
        $prettyKey = ucfirst(str_replace('_', ' ', $key));
    @endphp

    @if ($key === 'fees')
        @php
            $fees = is_array($value) ? $value : json_decode($value, true);
            $amounts = is_array($values['amounts'] ?? []) ? $values['amounts'] : json_decode($values['amounts'] ?? '[]', true);
            $totalAmount = 0;
            $totalItems = 0;

            foreach ($fees as $feeId => $qty) {
                $amount = $amounts[$feeId] ?? 0;
                $totalAmount += $amount * $qty;
                $totalItems += $qty;
            }

            $modalId = 'detailsModal-' . ($loop->parent->index ?? $loop->index);
        @endphp

        <div>
            <strong>{{ $prettyKey }}:</strong>
            {{ $totalItems }} items, ₱{{ number_format($totalAmount, 2) }} total
            <button class="btn btn-sm btn-link p-0 ms-1" type="button"
                data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                View details
            </button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="{{ $modalId }}Label">Fee Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="mb-0">
                            @foreach ($fees as $feeId => $qty)
                                @php
                                    $feeName = \App\Models\Fee::find($feeId)?->fee_name ?? "Fee ID {$feeId}";
                                    $amount = $amounts[$feeId] ?? 0;
                                @endphp
                                <li>{{ $feeName }} — {{ $qty }} × ₱{{ number_format($amount, 2) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    @elseif ($key === 'amounts')
        {{-- Skip raw amounts because fees already display them --}}
        @continue

    @else
        <div>
            <strong>{{ $prettyKey }}:</strong>
            {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}
        </div>
    @endif
@empty
    <em class="text-muted">None</em>
@endforelse
