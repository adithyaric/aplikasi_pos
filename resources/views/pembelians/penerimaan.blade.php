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
                                    <option value="draft" {{ old('receipt_status', $pembelian->receipt_status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    {{-- <option value="validated"{{ old('receipt_status', $pembelian->receipt_status) == 'validated' ? 'selected' : '' }}>Validated</option> --}}
                                    <option value="completed" {{ old('receipt_status', $pembelian->receipt_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Bukti Foto (Optional)</label>
                                <input type="file" name="receipt_photo" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG. Max: 2MB</small><br>
                                <a href="{{ asset('storage/' . $pembelian->receipt_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $pembelian->receipt_photo) }}"
                                        style="max-width: 200px;">
                                </a>
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
                                        <th width="120">Qty Diterima <span class="text-danger">*</span></th>
                                        <th width="150">Expired Date</th>
                                        {{-- <th width="200">Serial Numbers</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian->pembelianProducts as $item)
                                        @php
                                            $stockPembelian = $pembelian
                                                ->stockPembelians()
                                                ->where('product_id', $item->product_id)
                                                ->sum('qty');
                                            $maxDiterima = $stockPembelian;
                                            $isSerialized = $item->product->is_serialized;
                                            $stockMarket = \App\Models\Stock::where([
                                                'pembelian_id' => $pembelian->id,
                                                'product_id' => $item->product_id,
                                            ])->sum('qty');

                                            // Get existing serials from StockPembelian (for reference)
                                            $existingSerials = [];
                                            if ($isSerialized) {
                                                $existingSerials = $pembelian
                                                    ->stockPembelians()
                                                    ->where('product_id', $item->product_id)
                                                    ->where('qty', '>', 0)
                                                    ->whereNotNull('serial_number')
                                                    ->pluck('serial_number')
                                                    ->toArray();
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $item->product->name }}
                                                @if ($isSerialized && !empty($existingSerials))
                                                    <br><small class="text-muted">
                                                        <strong>PO Serials:</strong>
                                                        {{ implode(', ', array_slice($existingSerials, 0, 3)) }}
                                                        @if (count($existingSerials) > 3)
                                                            ... (+{{ count($existingSerials) - 3 }} more)
                                                        @endif
                                                    </small>
                                                @endif
                                                <input type="hidden" name="items[{{ $loop->index }}][product_id]"
                                                    value="{{ $item->product_id }}">
                                                <input type="hidden" name="items[{{ $loop->index }}][is_serialized]"
                                                    value="{{ $isSerialized ? 1 : 0 }}">
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                <span
                                                    class="label {{ $stockPembelian > 0 ? 'label-warning' : 'label-default' }}">
                                                    {{ $stockPembelian }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $loop->index }}][qty_diterima]"
                                                    class="form-control input-sm qty-diterima"
                                                    data-index="{{ $loop->index }}"
                                                    data-serialized="{{ $isSerialized ? 1 : 0 }}" min="0"
                                                    max="{{ $maxDiterima }}"
                                                    value="{{ old('items.' . $loop->index . '.qty_diterima', $maxDiterima) }}"
                                                    {{ $maxDiterima == 0 ? 'disabled' : 'required' }}>
                                                <small class="text-muted">Diterima:{{ $stockMarket }}</small>
                                                <br>
                                                <small class="text-muted">Max: {{ $maxDiterima }}</small>

                                            </td>
                                            <td>
                                                <input type="date" name="items[{{ $loop->index }}][expired_at]"
                                                    class="form-control input-sm"
                                                    value="{{ old('items.' . $loop->index . '.expired_at') }}"
                                                    {{ $maxDiterima == 0 ? 'disabled' : '' }}>
                                            </td>
                                            //TODO input sku (tidak ganti walaupun input partial)
                                            {{-- <td> --}}
                                                {{-- @if ($isSerialized) --}}
                                                    {{-- <textarea name="items[{{ $loop->index }}][serial_numbers]" class="form-control input-sm serial-input" --}}
                                                        {{-- data-index="{{ $loop->index }}" rows="3" --}}
                                                        {{-- placeholder="Scan or enter actual serial numbers received (one per line)" --}}
                                                        {{-- {{ $maxDiterima == 0 ? 'disabled' : '' }}>{{ old('items.' . $loop->index . '.serial_numbers') }}</textarea> --}}
                                                    {{-- <small class="text-muted serial-count" --}}
                                                        {{-- id="serial-count-{{ $loop->index }}">0 serial(s) entered</small> --}}
                                                    {{-- <br><small class="text-info"><i class="fa fa-info-circle"></i> Enter --}}
                                                        {{-- ACTUAL serials received (can differ from PO)</small> --}}
                                                {{-- @else --}}
                                                    {{-- <span class="text-muted">N/A (Bulk item)</span> --}}
                                                {{-- @endif --}}
                                            {{-- </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('pembelian.index') }}" class="btn btn-default">Kembali</a>
                            @if (!$pembelian->is_published)
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Terima Barang
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
