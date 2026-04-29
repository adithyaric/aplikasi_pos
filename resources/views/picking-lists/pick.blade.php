@extends('layouts.master')
@section('title', 'Picking Interface')
@section('container')
    <section class="content-header">
        <h1>Picking: {{ $pickingList->code }}</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <h3 class="box-title">Scan & Pick Items</h3>
                    </div>
                    <div class="box-body">

                        {{-- Location filter bar --}}
                        @php
                            $lokasiList = $pickingList->items
                                ->map(fn($i) => $i->location ?? $i->product?->lokasi)
                                ->unique()->filter()->sort()->values();
                        @endphp
                        <div class="row" style="margin-bottom:12px;">
                            <div class="col-sm-4">
                                <label>Filter Lokasi</label>
                                <select id="filter-lokasi" class="form-control">
                                    <option value="">Semua Lokasi</option>
                                    @foreach ($lokasiList as $lok)
                                        <option value="{{ $lok }}">{{ $lok }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2" style="padding-top:25px;">
                                <a id="btn-export"
                                   href="{{ route('laporan.pickinglist', $pickingList->id) }}"
                                   class="btn btn-default">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('picking-lists.bulk-update', $pickingList->id) }}" method="POST">
                            @csrf
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered table-striped" id="pick-table">
                                    <thead>
                                        <tr>
                                            <th>NO</th>
                                            <th>Barcode</th>
                                            <th>Product</th>
                                            <th>Location</th>
                                            <th>SKU</th>
                                            <th>Qty to Pick</th>
                                            <th>Qty Picked</th>
                                            <th>Val Barcode</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pickingList->items as $item)
                                            @php $lokasi = $item->location ?? $item->product?->lokasi; @endphp
                                            <tr data-lokasi="{{ $lokasi }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->product->code }}</td>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ $lokasi }}</td>
                                                <td>{{ $item->sku ?? '-' }}</td>
                                                <td>{{ $item->qty_to_pick }}</td>
                                                <td>
                                                    <input type="number" name="items[{{ $item->id }}][qty_picked]"
                                                        class="form-control qty-input" value="{{ $item->qty_picked }}" min="0"
                                                        max="{{ $item->qty_to_pick }}" style="width: 80px;">
                                                </td>
                                                <td>
                                                    <div style="display:flex;gap:4px;align-items:center;">
                                                        <input type="text" class="form-control val-barcode-input"
                                                            placeholder="Scan barcode" style="width: 120px;">
                                                        <button type="button"
                                                            class="btn btn-warning btn-validasi"
                                                            data-item-id="{{ $item->id }}"
                                                            data-update-url="{{ route('picking-list-items.update', $item->id) }}">
                                                            Validasi
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($item->is_picked)
                                                        <span class="label label-success">&#10003; PICKED</span>
                                                    @else
                                                        <span class="label label-warning">PENDING</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="8"></td>
                                            <td>
                                                <button type="submit" class="btn btn-primary">Bulk Update</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer text-center">
                        <form action="{{ route('picking-lists.complete', $pickingList->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> Complete Picking
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
<script>
    var baseExportUrl = '{{ route('laporan.pickinglist', $pickingList->id) }}';

    // Location filter — hide/show rows and update export URL
    $('#filter-lokasi').on('change', function () {
        var selected = $(this).val();

        $('#pick-table tbody tr[data-lokasi]').each(function () {
            if (!selected || $(this).data('lokasi') == selected) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('#btn-export').attr('href', selected
            ? baseExportUrl + '?lokasi=' + encodeURIComponent(selected)
            : baseExportUrl
        );
    });

    // Per-row Validasi button — builds and submits a hidden form server-side
    $(document).on('click', '.btn-validasi', function () {
        var $row       = $(this).closest('tr');
        var updateUrl  = $(this).data('update-url');
        var qtyPicked  = $row.find('.qty-input').val();
        var valBarcode = $row.find('.val-barcode-input').val();

        var $form = $('<form>', { method: 'POST', action: updateUrl }).hide();
        $form.append($('<input>', { type: 'hidden', name: '_token',    value: '{{ csrf_token() }}' }));
        $form.append($('<input>', { type: 'hidden', name: '_method',   value: 'PATCH' }));
        $form.append($('<input>', { type: 'hidden', name: 'qty_picked',  value: qtyPicked }));
        $form.append($('<input>', { type: 'hidden', name: 'val_barcode', value: valBarcode }));
        $('body').append($form);
        $form.submit();
    });
</script>
@endsection
