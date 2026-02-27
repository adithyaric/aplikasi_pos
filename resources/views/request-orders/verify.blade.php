@extends('layouts.master')

@section('title', 'Verifikasi Request Order')

@section('container')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Verifikasi Request Order: {{ $requestOrder->code }}</h3>
                    </div>

                    <form action="{{ route('request-orders.process-verification', $requestOrder) }}" method="POST">
                        @csrf
                        <div class="box-body">
                            {{-- Info Request --}}
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">Kode Request</th>
                                    <td>{{ $requestOrder->code }}</td>
                                </tr>
                                <tr>
                                    <th>Owner (Outlet)</th>
                                    <td>{{ $requestOrder->owner->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Requested By</th>
                                    <td>{{ $requestOrder->requestedBy->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Request</th>
                                    <td>{{ $requestOrder->request_date->format('d-m-Y') }}</td>
                                </tr>
                                @if ($requestOrder->notes)
                                    <tr>
                                        <th>Catatan Umum</th>
                                        <td>{{ $requestOrder->notes }}</td>
                                    </tr>
                                @endif
                            </table>

                            <hr>
                            <h4>Item yang diminta</h4>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>Produk</td>
                                        <td>Qty Warehouse</td>
                                        <td>Qty Request</td>
                                        <td>Qty Approved</td>
                                        <td>Status</td>
                                        <td>Catatan Item</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestOrder->items as $index => $item)
                                        @php
                                            $stock = $item->stock;
                                            $availableStock = $stock ? $stock->qty_available : 0;
                                            $maxQty = min($item->qty_requested, $availableStock);
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $item->product->name }}
                                                <br><small class="text-muted">SKU: {{ $stock->sku ?? 'N/A' }}</small>
                                                <input type="hidden" name="items[{{ $index }}][id]"
                                                    value="{{ $item->id }}">
                                            </td>
                                            <td>
                                                <span
                                                    class="label {{ $availableStock >= $item->qty_requested ? 'label-success' : 'label-warning' }}">
                                                    {{ $availableStock }}
                                                </span>
                                            </td>
                                            <td>{{ $item->qty_requested }}</td>
                                            <td>
                                                <input type="number" class="form-control qty-approved"
                                                    name="items[{{ $index }}][qty_approved]"
                                                    value="{{ old('items.' . $index . '.qty_approved', $item->qty_approved ?? $maxQty) }}"
                                                    min="0" max="{{ $maxQty }}"
                                                    data-max="{{ $maxQty }}" data-available="{{ $availableStock }}"
                                                    step="1" required>
                                                <small class="text-muted">Max: {{ $maxQty }}</small>
                                                @error('items.' . $index . '.qty_approved')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <select class="form-control item-status"
                                                    name="items[{{ $index }}][item_status]"
                                                    data-index="{{ $index }}" required>
                                                    <option value="approved">Approved</option>
                                                    <option value="partial">Partial</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control"
                                                    name="items[{{ $index }}][notes]"
                                                    value="{{ old('items.' . $index . '.notes', $item->notes) }}"
                                                    placeholder="Notes">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="form-group">
                                <label for="verification_notes">Catatan Verifikasi (opsional)</label>
                                <textarea class="form-control" name="verification_notes" rows="3" placeholder="Catatan verifikasi...">{{ old('verification_notes', $requestOrder?->verification_notes) }}</textarea>
                            </div>
                            @if ($requestOrder->status == 'pending')
                                <button type="submit" class="btn btn-primary">Proses Verifikasi</button>
                            @endif
                        </div>
                    </form>
                    <div class="box-footer">
                        @if (($requestOrder->status == 'approved' || $requestOrder->status == 'partial') && !isset($requestOrder->pickingList))
                            <form action="{{ route('picking-lists.generate', $requestOrder->id) }}" method="post">
                                @csrf
                                <button class="btn btn-primary">
                                    <i class="fa fa-list"></i> Generate Picking List
                                </button>
                            </form>
                            <hr>
                        @endif
                        <a href="{{ route('request-orders.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Auto-adjust status based on qty_approved
            $('.qty-approved').on('input', function() {
                var $row = $(this).closest('tr');
                var qtyApproved = parseInt($(this).val()) || 0;
                var maxQty = parseInt($(this).attr('max')) || 0;
                var availableStock = parseInt($(this).data('available')) || 0;
                var qtyRequested = parseInt($row.find('td:eq(2)').text()) || 0;
                var $status = $row.find('.item-status');

                // Validate against max
                if (qtyApproved > maxQty) {
                    $(this).val(maxQty);
                    qtyApproved = maxQty;
                    alert('Qty approved cannot exceed available stock (' + availableStock +
                        ') or requested qty (' + qtyRequested + ')');
                }

                // Auto-set status
                if (qtyApproved === 0) {
                    $status.val('rejected');
                } else if (qtyApproved < qtyRequested) {
                    $status.val('partial');
                } else {
                    $status.val('approved');
                }
            });

            // When status changes to rejected, set qty to 0
            $('.item-status').on('change', function() {
                var $row = $(this).closest('tr');
                var $qtyInput = $row.find('.qty-approved');

                if ($(this).val() === 'rejected') {
                    $qtyInput.val(0);
                } else if ($qtyInput.val() == 0) {
                    var maxQty = parseInt($qtyInput.attr('max')) || 0;
                    $qtyInput.val(maxQty);
                }
            });

            // Form validation before submit
            // $('form').on('submit', function(e) {
            //     var hasError = false;

            //     $('.qty-approved').each(function() {
            //         var qtyApproved = parseInt($(this).val()) || 0;
            //         var maxQty = parseInt($(this).attr('max')) || 0;
            //         var availableStock = parseInt($(this).data('available')) || 0;

            //         if (qtyApproved > maxQty) {
            //             hasError = true;
            //             alert('Error: Approved quantity exceeds available stock for some products');
            //             return false;
            //         }
            //     });

            //     if (hasError) {
            //         e.preventDefault();
            //         return false;
            //     }
            // });
        });
    </script>
@endsection
