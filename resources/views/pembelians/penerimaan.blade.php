@extends('layouts.master')
@section('title', 'Penerimaan Barang')
@section('container')
    <section class="content-header">
        <h1>PENERIMAAN BARANG</h1>
    </section>

    <section class="content">
        <form action="{{ route('pembelian.store-penerimaan', $pembelian) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Informasi PO</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Kode PO</th>
                                    <td>{{ $pembelian->code }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td>{{ $pembelian->supplier->name }}</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Detail Penerimaan</h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label>Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" name="receipt_date" class="form-control"
                                    value="{{ old('receipt_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group">
                                <label>PIC Penerima <span class="text-danger">*</span></label>
                                <input type="text" name="receipt_pic" class="form-control"
                                    value="{{ old('receipt_pic', auth()->user()->name) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Status Penerimaan <span class="text-danger">*</span></label>
                                <select name="receipt_status" class="form-control" required>
                                    <option value="draft"
                                        {{ old('receipt_status', $pembelian->receipt_status) == 'draft' ? 'selected' : '' }}>
                                        Draft</option>
                                    {{-- <option value="validated"{{ old('receipt_status', $pembelian->receipt_status) == 'validated' ? 'selected' : '' }}>Validated</option> --}}
                                    <option value="completed"
                                        {{ old('receipt_status', $pembelian->receipt_status) == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Bukti Foto (Optional)</label>
                                <input type="file" name="receipt_photo" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG. Max: 2MB</small><br>
                                @if ($pembelian->receipt_photo)
                                <a href="{{ asset('storage/' . $pembelian->receipt_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $pembelian->receipt_photo) }}"
                                        style="max-width: 200px;">
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Input Barang Diterima</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="30">No</th>
                                        <th>Product</th>
                                        <th width="80">Qty PO</th>
                                        <th width="100">Qty Tersisa</th>
                                        <th width="120">SKU <span class="text-danger">*</span></th>
                                        <th width="150">Expired Date</th>
                                        <th width="120">Qty Diterima <span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    @foreach ($pembelian->pembelianProducts as $item)
                                        @php
                                            // Get existing stocks for this product in this pembelian
                                            $existingStocks = $pembelian
                                                ->stocks()
                                                ->where('product_id', $item->product_id)
                                                ->get();

                                            // If no stocks exist, show one empty row
                                            if ($existingStocks->isEmpty()) {
                                                $existingStocks = collect([null]);
                                            }
                                        @endphp

                                        @foreach ($existingStocks as $stockIndex => $stock)
                                            <tr data-product-id="{{ $item->product_id }}">
                                                <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $item->product->name }}
                                                    <input type="hidden"
                                                        name="items[{{ $loop->parent->index }}_{{ $stockIndex }}][product_id]"
                                                        value="{{ $item->product_id }}">
                                                    @if ($stock)
                                                        <input type="hidden"
                                                            name="items[{{ $loop->parent->index }}_{{ $stockIndex }}][stock_id]"
                                                            value="{{ $stock->id }}">
                                                    @endif
                                                </td>
                                                <td>{{ $item->qty }}</td>
                                                <td>
                                                    @if ($stock)
                                                        <span class="label label-success">{{ $item->qty - $stock->qty }} In Stock</span>
                                                    @else
                                                        <span class="label label-warning">New</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $loop->parent->index }}_{{ $stockIndex }}][sku]"
                                                        class="form-control input-sm" placeholder="Enter SKU"
                                                        value="{{ old('items.' . $loop->parent->index . '_' . $stockIndex . '.sku', $stock->sku ?? '') }}"
                                                        required>
                                                </td>
                                                <td>
                                                    <input type="date"
                                                        name="items[{{ $loop->parent->index }}_{{ $stockIndex }}][expired_at]"
                                                        class="form-control input-sm"
                                                        value="{{ old('items.' . $loop->parent->index . '_' . $stockIndex . '.expired_at', $stock?->expired_at?->format('Y-m-d')) }}">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $loop->parent->index }}_{{ $stockIndex }}][qty_diterima]"
                                                        class="form-control input-sm" min="1" max="{{ $item->qty }}"
                                                        value="{{ old('items.' . $loop->parent->index . '_' . $stockIndex . '.qty_diterima', $stock->qty ?? $item->qty) }}"
                                                        required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('pembelian.index') }}" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Penerimaan
                            </button>
                            @if (!$pembelian->is_published && $pembelian->stocks()->count() > 0)
                                <button type="submit" name="receipt_status" value="completed" class="btn btn-success">
                                    <i class="fa fa-check"></i> Simpan & Publish
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            // Count serial numbers on input
            $('.serial-input').on('input', function() {
                var index = $(this).data('index');
                var serials = $(this).val().split('\n').filter(function(line) {
                    return line.trim() !== '';
                });
                var count = serials.length;
                $('#serial-count-' + index).text(count + ' serial(s) entered');

                // Update qty_diterima for serialized items
                var qtyInput = $('input[data-index="' + index + '"].qty-diterima');
                if (qtyInput.data('serialized') == 1) {
                    qtyInput.val(count);
                }
            });

            // Validate qty vs serial numbers on submit
            $('form').on('submit', function(e) {
                var hasError = false;

                $('.qty-diterima').each(function() {
                    var index = $(this).data('index');
                    var isSerialized = $(this).data('serialized');
                    var qtyDiterima = parseInt($(this).val()) || 0;

                    if (isSerialized == 1 && qtyDiterima > 0) {
                        var serialInput = $('.serial-input[data-index="' + index + '"]');
                        var serials = serialInput.val().split('\n').filter(function(line) {
                            return line.trim() !== '';
                        });

                        if (serials.length !== qtyDiterima) {
                            alert('Error: Qty diterima (' + qtyDiterima +
                                ') must match number of serial numbers (' + serials.length +
                                ') for serialized products');
                            hasError = true;
                            return false;
                        }
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
