@extends('layouts.master')
@section('title', 'Penerimaan Barang')
@section('container')
    <section class="content-header">
        <h1>PENERIMAAN BARANG</h1>
    </section>

    <section class="content">
        <form action="{{ route('pembelian.store-penerimaan', $pembelian) }}" method="POST">
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
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Input Barang Diterima</h3>
                        </div>
                        <div class="box-body table-responsive text-nowrap">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>Qty PO</th>
                                        <th>Qty Di Warehouse</th>
                                        <th>Qty Sudah Diterima</th>
                                        <th>Qty Diterima Sekarang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian->pembelianProducts as $item)
                                        @php
                                            $stockPembelian = $pembelian
                                                ->stockPembelians()
                                                ->where('product_id', $item->product_id)
                                                ->sum('qty');
                                            $stockMarket = \App\Models\Stock::where([
                                                'pembelian_id' => $pembelian->id,
                                                'product_id' => $item->product_id,
                                            ])->sum('qty');
                                            $maxDiterima = $stockPembelian;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $item->product->name }}
                                                <input type="hidden" name="items[{{ $loop->index }}][product_id]"
                                                    value="{{ $item->product_id }}">
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                <span class="label label-warning">{{ $stockPembelian }}</span>
                                            </td>
                                            <td>
                                                <span class="label label-success">{{ $stockMarket }}</span>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $loop->index }}][qty_diterima]"
                                                    class="form-control" min="0" max="{{ $maxDiterima }}"
                                                    value="{{ $maxDiterima }}" required style="width: 100px;">
                                                <small class="text-muted">Max: {{ $maxDiterima }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('pembelian.index') }}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> Terima Barang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
