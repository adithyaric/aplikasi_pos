@extends('layouts.master')
@section('title', 'Penerimaan Barang')
@section('container')
    @php
        $isLocked = $pembelian->receipt_status === 'completed' || $pembelian->stocks->count() > 0;
    @endphp

    <section class="content-header">
        <h1>Penerimaan Barang <small>{{ $pembelian->code }}</small></h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('penerimaan.index') }}">Penerimaan Barang</a></li>
            <li class="active">{{ $pembelian->code }}</li>
        </ol>
    </section>

    <section class="content">
        <form
            action="{{ $isLocked ? route('pembelian.update-penerimaan', $pembelian) : route('pembelian.store-penerimaan', $pembelian) }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-12">
                    <div class="box {{ $isLocked ? 'box-success' : 'box-warning' }}">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-cubes"></i> Input Barang Diterima
                            </h3>
                            <div class="box-tools pull-right">
                                @if($isLocked)
                                    <span class="label label-success">
                                        <i class="fa fa-lock"></i> Tersimpan
                                    </span>
                                @else
                                    <span class="label label-warning">
                                        <i class="fa fa-pencil"></i> Belum disimpan
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="box-body table-responsive text-nowrap" style="padding:0">
                            <table class="table table-bordered table-striped" style="margin:0">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Product</th>
                                        <th>Satuan</th>
                                        <th>Konversi</th>
                                        <th class="text-center">Qty PO</th>
                                        <th>SKU</th>
                                        <th>Expired</th>
                                        <th class="text-center">Qty Terima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian->pembelianProducts as $item)
                                        @php
                                            $existingStocks = $pembelian->stocks()
                                                ->where('product_id', $item->product_id)
                                                ->get();
                                            if ($existingStocks->isEmpty()) {
                                                $existingStocks = collect([null]);
                                            }
                                        @endphp
                                        @foreach ($existingStocks as $stockIndex => $stock)
                                            <tr>
                                                <td class="text-center text-muted">
                                                    <small>{{ $loop->parent?->iteration }}.{{ $loop->iteration }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $item->product->name }}</strong>
                                                    <br><small class="text-muted">@currency($item->harga_beli)</small>

                                                    @if(!$isLocked)
                                                        <input type="hidden"
                                                            name="items[{{ $loop->parent?->index }}_{{ $stockIndex }}][product_id]"
                                                            value="{{ $item->product_id }}">
                                                        @if($stock)
                                                            <input type="hidden"
                                                                name="items[{{ $loop->parent?->index }}_{{ $stockIndex }}][stock_id]"
                                                                value="{{ $stock->id }}">
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ $item->product->satuan ?? '-' }}</td>
                                                <td>{{ $item->product->konversiDisplay($item->qty) }}</td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td>
                                                    @if($isLocked)
                                                        <span class="text-success"><strong>{{ $stock->sku ?? '-' }}</strong></span>
                                                    @else
                                                        <input type="text"
                                                            name="items[{{ $loop->parent?->index }}_{{ $stockIndex }}][sku]"
                                                            class="form-control input-sm"
                                                            placeholder="SKU"
                                                            value="{{ old('items.' . $loop->parent?->index . '_' . $stockIndex . '.sku', $stock->sku ?? '') }}"
                                                            required>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isLocked)
                                                        <span class="text-muted">{{ $stock?->expired_at?->format('d/m/Y') ?? '-' }}</span>
                                                    @else
                                                        <input type="date"
                                                            name="items[{{ $loop->parent?->index }}_{{ $stockIndex }}][expired_at]"
                                                            class="form-control input-sm"
                                                            value="{{ old('items.' . $loop->parent?->index . '_' . $stockIndex . '.expired_at', $stock?->expired_at?->format('Y-m-d')) }}">
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isLocked)
                                                        <span class="label label-success">{{ $stock->qty ?? 0 }}</span>
                                                    @else
                                                        <input type="number"
                                                            name="items[{{ $loop->parent?->index }}_{{ $stockIndex }}][qty_diterima]"
                                                            class="form-control input-sm text-center"
                                                            min="1" max="{{ $item->qty }}"
                                                            value="{{ old('items.' . $loop->parent?->index . '_' . $stockIndex . '.qty_diterima', $stock->qty ?? $item->qty) }}"
                                                            required>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($isLocked)
                            <div class="box-footer text-muted">
                                <i class="fa fa-lock"></i> Items sudah tersimpan dan tidak bisa diubah.
                                @if($pembelian->stocks->count())
                                    <a href="{{ route('laporan.penerimaan', [$pembelian->id, 'po']) }}" class="btn btn-info btn-xs pull-right">
                                        <i class="fa fa-file-excel-o"></i> Export GR
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="box-footer text-muted">
                                <i class="fa fa-info-circle"></i> Isi SKU & qty lalu klik <strong>Simpan Penerimaan</strong>.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            {{-- Detail Penerimaan --}}
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-clipboard"></i> Detail Penerimaan</h3>
                                    @if($isLocked)
                                        <div class="box-tools pull-right">
                                            <span class="label label-success"><i class="fa fa-lock"></i> Items Terkunci</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Nomor Goods Receipt <span class="text-danger">*</span></label>
                                        <input type="text" name="code_gr" class="form-control"
                                            value="{{ old('code_gr', $pembelian->code_gr ?? str_replace('PO', 'GR', $pembelian->code)) }}"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Penerimaan <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="receipt_date" class="form-control"
                                            value="{{ old('receipt_date', $pembelian->receipt_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label>PIC Penerima <span class="text-danger">*</span></label>
                                        <input type="text" name="receipt_pic" class="form-control"
                                            value="{{ old('receipt_pic', $pembelian->receipt_pic ?? auth()->user()->name) }}"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label>Status Penerimaan <span class="text-danger">*</span></label>
                                        <select name="receipt_status" class="form-control" required>
                                            <option value="draft"      {{ old('receipt_status', $pembelian->receipt_status) == 'draft'     ? 'selected' : '' }}>Draft</option>
                                            <option value="validated"  {{ old('receipt_status', $pembelian->receipt_status) == 'validated' ? 'selected' : '' }}>Validated</option>
                                            <option value="completed"  {{ old('receipt_status', $pembelian->receipt_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                        @if(!$isLocked)
                                            <span class="help-block">
                                                <i class="fa fa-info-circle"></i> Set ke <strong>Completed</strong> untuk publish stok ke gudang. Setelah itu items tidak bisa diubah.
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Bukti Foto <small class="text-muted">(opsional)</small></label>
                                        <input type="file" name="receipt_photo" class="form-control" accept="image/*">
                                        <small class="text-muted">JPG/PNG, max 2MB</small>
                                        @if($pembelian->receipt_photo)
                                            <div style="margin-top:6px">
                                                <a href="{{ asset('storage/' . $pembelian->receipt_photo) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $pembelian->receipt_photo) }}"
                                                        style="max-width:100%; max-height:120px; border-radius:4px; border:1px solid #ddd;">
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <a href="{{ route('penerimaan.index') }}" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary pull-right">
                                        <i class="fa fa-save"></i>
                                        {{ $isLocked ? 'Update Detail' : 'Simpan Penerimaan' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            {{-- PO Info --}}
                            <div class="box box-default">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Info Purchase Order</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <table class="table table-condensed" style="margin:0">
                                        <tr><th width="130">Kode PO</th><td><strong>{{ $pembelian->code }}</strong></td></tr>
                                        <tr><th>Supplier</th><td>{{ $pembelian->supplier->name }}</td></tr>
                                        <tr><th>Total</th><td>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td></tr>
                                        <tr>
                                            <th>Status Bayar</th>
                                            <td>
                                                @php $ps = $pembelian->pembelianTransaction?->status ?? 'unpaid'; @endphp
                                                <span class="label label-{{ $ps === 'paid' ? 'success' : ($ps === 'partial' ? 'warning' : 'danger') }}">
                                                    {{ strtoupper($ps) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection