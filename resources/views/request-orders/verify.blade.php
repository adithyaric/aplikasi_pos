@extends('layouts.master')
@section('title', 'Verifikasi Request Order')
@section('container')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            @if ($requestOrder->items()->whereNotNull('stock_id')->count() == 0)
                                Assign Stocks - {{ $requestOrder->code }}
                            @else
                                Verifikasi Request Order - {{ $requestOrder->code }}
                            @endif
                        </h3>
                    </div>

                    {{-- Info Request --}}
                    <div class="box-body">
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

                        @if ($requestOrder->items()->whereNotNull('stock_id')->count() == 0)
                            {{-- STEP 1: Assign Stocks --}}
                            <h4>Assign Stocks to Request</h4>
                            <form action="{{ route('request-orders.update-stocks', $requestOrder) }}" method="POST"
                                id="assign-form">
                                @csrf
                                <div id="stock-assignment-container">
                                    @foreach ($requestOrder->items as $index => $item)
                                        <div class="panel panel-default item-panel" data-item-id="{{ $item->id }}">
                                            <div class="panel-heading">
                                                <strong>{{ $item->product->name }}</strong>
                                                - Requested: <span class="label label-info">{{ $item->qty_requested }}</span>
                                                @if($item->product->konversi_qty && $item->product->satuan_besar)
                                                    <small class="text-muted">({{ $item->product->konversiDisplay($item->qty_requested) }})</small>
                                                @endif
                                                - Remaining: <span class="remaining-qty label label-warning">{{ $item->qty_requested }}</span>
                                            </div>
                                            <div class="panel-body">
                                                <table class="table table-bordered stock-assignment-table">
                                                    <thead>
                                                        <tr>
                                                            <th width="40%">SKU</th>
                                                            <th width="20%">Available</th>
                                                            <th width="20%">Assign Qty</th>
                                                            <th width="20%">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="stock-rows">
                                                        <tr class="stock-row">
                                                            <td>
                                                                <select
                                                                    name="stock_assignments[{{ $index }}_0][stock_id]"
                                                                    class="form-control stock-select select2"
                                                                    data-product-id="{{ $item->product_id }}" required>
                                                                    <option value="">Select SKU</option>
                                                                    @foreach ($item->product->stocks()->where('qty_available', '>', 0)->orderBy('expired_at')->get() as $stock)
                                                                        <option value="{{ $stock->id }}"
                                                                            data-available="{{ $stock->qty_available }}">
                                                                            {{ $stock->sku }} (Available:
                                                                            {{ $stock->qty_available }},
                                                                            Exp:
                                                                            {{ $stock->expired_at?->format('d-m-Y') ?? 'N/A' }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden"
                                                                    name="stock_assignments[{{ $index }}_0][item_id]"
                                                                    value="{{ $item->id }}">
                                                            </td>
                                                            <td class="available-qty">-</td>
                                                            <td>
                                                                <input type="number"
                                                                    name="stock_assignments[{{ $index }}_0][qty]"
                                                                    class="form-control assign-qty" min="1"
                                                                    value="{{ $item->qty_requested }}" required>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm add-stock-row"
                                                                    data-item-id="{{ $item->id }}">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Stock Assignment
                                </button>
                            </form>
                        @else
                            {{-- STEP 2: Approve Quantities --}}
                            <h4>Approve Quantities</h4>
                            <form action="{{ route('request-orders.process-verification', $requestOrder) }}"
                                method="POST">
                                @csrf
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <td>Produk</td>
                                            <td>SKU</td>
                                            <td>Qty Available</td>
                                            <td>Qty Requested</td>
                                            <td>Qty Approved</td>
                                            <td>Status</td>
                                            <td>Notes</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requestOrder->items as $index => $item)
                                            @php
                                                $stock = $item->stock;
                                                $currentReserved = $item->qty_approved ?? 0;
                                                $availableStock = $stock ? $stock->qty_available : 0;
                                                $totalAvailable = $availableStock + $currentReserved; // Include currently reserved qty
                                                $maxQty = min($item->qty_requested, $totalAvailable);
                                            @endphp
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td><small class="text-muted">{{ $stock->sku ?? 'N/A' }}</small></td>
                                                <td>
                                                    <span
                                                        class="label {{ $availableStock >= $item->qty_requested ? 'label-success' : 'label-warning' }}">
                                                        {{ $availableStock }}
                                                    </span>
                                                    @if ($item->qty_approved > 0)
                                                        <br><small class="text-info">(Reserved:
                                                            {{ $item->qty_approved }})</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $item->qty_requested }}
                                                    @if($item->product->konversi_qty && $item->product->satuan_besar)
                                                        <br><small class="text-muted">{{ $item->product->konversiDisplay($item->qty_requested) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control qty-approved"
                                                        name="items[{{ $index }}][qty_approved]"
                                                        value="{{ old('items.' . $index . '.qty_approved', $item->qty_approved ?? $maxQty) }}"
                                                        min="0" max="{{ $maxQty }}"
                                                        data-max="{{ $maxQty }}"
                                                        data-available="{{ $totalAvailable }}"
                                                        data-requested="{{ $item->qty_requested }}" step="1"
                                                        required @readonly(isset($requestOrder->pickingList))>
                                                    <small class="text-muted">Max: {{ $maxQty }}</small>
                                                    <input type="hidden" name="items[{{ $index }}][id]"
                                                        value="{{ $item->id }}">
                                                    @error('items.' . $index . '.qty_approved')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <select class="form-control item-status"
                                                        name="items[{{ $index }}][item_status]"
                                                        data-index="{{ $index }}" required @readonly(isset($requestOrder->pickingList))>
                                                        <option value="approved"
                                                            {{ old('items.' . $index . '.item_status', $item->item_status) == 'approved' ? 'selected' : '' }}>
                                                            Approved</option>
                                                        <option value="partial"
                                                            {{ old('items.' . $index . '.item_status', $item->item_status) == 'partial' ? 'selected' : '' }}>
                                                            Partial</option>
                                                        <option value="rejected"
                                                            {{ old('items.' . $index . '.item_status', $item->item_status) == 'rejected' ? 'selected' : '' }}>
                                                            Rejected</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        name="items[{{ $index }}][notes]"
                                                        value="{{ old('items.' . $index . '.notes', $item->notes) }}"
                                                        placeholder="Notes" @readonly(isset($requestOrder->pickingList))>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="form-group">
                                    <label for="verification_notes">Catatan Verifikasi (opsional)</label>
                                    <textarea @readonly(isset($requestOrder->pickingList)) class="form-control" name="verification_notes" rows="3">{{ old('verification_notes', $requestOrder->verification_notes) }}</textarea>
                                </div>
                                @if (!isset($requestOrder->pickingList))
                                <button type="submit" class="btn btn-primary">
                                    @if ($requestOrder->status == 'pending')
                                        Proses Verifikasi
                                    @else
                                        Update Verifikasi
                                    @endif
                                </button>
                                @endif
                            </form>
                        @endif
                        @if($requestOrder->additionalNotes->isNotEmpty())
                            <hr>
                            <h4>Sample</h4>
                            <table class="table table-bordered table-condensed" style="max-width:600px">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Kategori</th>
                                        <th width="80" class="text-center">Qty</th>
                                        <th>Nama PJ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requestOrder->additionalNotes as $i => $note)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $note->kategori }}</td>
                                        <td class="text-center">{{ $note->qty }}</td>
                                        <td>{{ $note->nama_pj ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    <div class="box-footer">
                        @if (($requestOrder->status == 'approved' || $requestOrder->status == 'partial') && !isset($requestOrder->pickingList))
                            <form action="{{ route('picking-lists.generate', $requestOrder->id) }}" method="post">
                                @csrf
                                <button class="btn btn-success">
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
            let rowCounters = {};

            // Update available qty and auto-adjust assign qty when stock selected
            $(document).on('change', '.stock-select', function() {
                const row = $(this).closest('tr');
                const available = parseInt($(this).find(':selected').data('available')) || 0;
                const assignQtyInput = row.find('.assign-qty');
                const currentValue = parseInt(assignQtyInput.val()) || 0;

                row.find('.available-qty').text(available);
                assignQtyInput.attr('max', available);

                // Auto-adjust qty: use available if current value exceeds it
                if (currentValue > available) {
                    assignQtyInput.val(available);
                }

                updateRemainingQty($(this).closest('.item-panel'));
            });

            // Update remaining when qty changes
            $(document).on('input', '.assign-qty', function() {
                updateRemainingQty($(this).closest('.item-panel'));
            });

// Add new stock row
$(document).on('click', '.add-stock-row', function() {
    const itemId = $(this).data('item-id');
    const panel = $(this).closest('.item-panel');
    const tbody = panel.find('.stock-rows');
    const productId = panel.find('.stock-select').first().data('product-id');
    const remainingQty = parseInt(panel.find('.remaining-qty').text()) || 0;

    if (!rowCounters[itemId]) rowCounters[itemId] = 1;
    else rowCounters[itemId]++;

    // Clone the first select's HTML to get all options, but then clear selection
    const firstSelectHtml = panel.find('.stock-select').first().html();

    const newRow = `
        <tr class="stock-row">
            <td>
                <select name="stock_assignments[${itemId}_${rowCounters[itemId]}][stock_id]"
                        class="form-control stock-select select2"
                        data-product-id="${productId}"
                        required
                        style="width:100%;">
                    ${firstSelectHtml}
                </select>
                <input type="hidden" name="stock_assignments[${itemId}_${rowCounters[itemId]}][item_id]" value="${itemId}">
            </td>
            <td class="available-qty">-</td>
            <td>
                <input type="number"
                       name="stock_assignments[${itemId}_${rowCounters[itemId]}][qty]"
                       class="form-control assign-qty"
                       min="1"
                       value="${remainingQty > 0 ? remainingQty : ''}"
                       required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-stock-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    tbody.append(newRow);

    // Initialize Select2 on the new select with width 100%
    let $newSelect = tbody.find('tr:last .stock-select');
    $newSelect.select2({ width: '100%' });
    $newSelect.val(''); // Ensure no option is selected by default

    updateRemainingQty(panel);
});

            // Remove stock row
            $(document).on('click', '.remove-stock-row', function() {
                const panel = $(this).closest('.item-panel');
                $(this).closest('tr').remove();
                updateRemainingQty(panel);
            });

            function updateRemainingQty(panel) {
                const requested = parseInt(panel.find('.label-info').text()) || 0;
                let totalAssigned = 0;

                panel.find('.assign-qty').each(function() {
                    totalAssigned += parseInt($(this).val()) || 0;
                });

                const remaining = requested - totalAssigned;
                const remainingLabel = panel.find('.remaining-qty');

                remainingLabel.text(remaining);
                if (remaining === 0) {
                    remainingLabel.removeClass('label-warning label-danger').addClass('label-success');
                } else if (remaining > 0) {
                    remainingLabel.removeClass('label-success label-danger').addClass('label-warning');
                } else {
                    remainingLabel.removeClass('label-success label-warning').addClass('label-danger');
                }
            }

            // Validate before submit
            $('#assign-form').on('submit', function(e) {
                let hasError = false;

                $('.item-panel').each(function() {
                    const remaining = parseInt($(this).find('.remaining-qty').text());
                    if (remaining !== 0) {
                        hasError = true;
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: 'Semua kuantitas permintaan harus terisi penuh!',
                        });
                        return false;
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    return false;
                }
            });

            // Auto-adjust status based on qty_approved (for approval step)
            $('.qty-approved').on('input', function() {
                const row = $(this).closest('tr');
                const qtyApproved = parseInt($(this).val()) || 0;
                const maxQty = parseInt($(this).attr('max')) || 0;
                const qtyRequested = parseInt($(this).data('requested')) || 0;
                const status = row.find('.item-status');

                if (qtyApproved > maxQty) {
                    $(this).val(maxQty);
                }

                if (qtyApproved === 0) {
                    status.val('rejected');
                } else if (qtyApproved < qtyRequested) {
                    status.val('partial');
                } else {
                    status.val('approved');
                }
            });

            $('.item-status').on('change', function() {
                const row = $(this).closest('tr');
                const qtyInput = row.find('.qty-approved');

                if ($(this).val() === 'rejected') {
                    qtyInput.val(0);
                } else if (qtyInput.val() == 0) {
                    const maxQty = parseInt(qtyInput.attr('max')) || 0;
                    qtyInput.val(maxQty);
                }
            });
        });
    </script>
@endsection
