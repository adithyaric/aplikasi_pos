@extends('layouts.master')

@section('title', 'Pembelian')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            INBOUND PROCESS (Stock Masuk Gudang)
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('pembelian.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    {{-- <td>Outlet</td> --}}
                                    <td>Nama</td>
                                    <td>items</td>
                                    <td>Total</td>
                                    <td>Status Bayar</td>
                                    <td>Status Penerimaan</td>
                                    <td>Aksi</td>
                                    <td>Export</td>
                                    {{-- <td>Posting</td> --}}
                                </tr>
                            </thead>
                            @foreach ($pembelians as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    {{-- <td>{{ $value->outlet?->name }}</td> --}}
                                    <td>{{ $value->code }}</td>
                                    <td>
                                        <ul>
                                            @foreach ($value->pembelianProducts as $item)
                                                <li>
                                                    @if (!empty($item->serial_numbers))
                                                        <small>[{{ implode(', ', $item->serial_numbers) }}]</small>
                                                    @endif
                                                    {{ $item->product->name }}: @currency($item->harga_beli) × {{ $item->qty }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>@currency($value->total)</td>
                                    <td>{{ $value->pembelianTransaction?->status }}</td>
                                    <td>{{ $value->receipt_status }}</td>
                                    <td>
                                        <a class=" btn-sm btn btn-success"
                                            href="{{ route('pembelian.pembayaran.edit', $value->id) }}"><i class="fa fa-credit-card"></i> Bayar</a>
                                        @if (!$value->is_published)
                                            <a class=" btn-sm btn btn-warning"
                                                href="{{ route('pembelian.edit', $value->id) }}">Edit</a>
                                            <form action="{{ route('pembelian.destroy', $value->id) }}" method="post"
                                                style="display: inline;">
                                                @method('delete')
                                                @csrf
                                                <button class="border-0 btn-sm btn btn-danger"
                                                    onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                        @else
                                            {{-- <a class=" btn-sm btn btn-warning" href="{{ route('pembelian.show', $value->id) }}">Print Barcode</a> --}}
                                            {{-- <a class=" btn-sm btn btn-success" href="{{ route('pembelian.print', $value->id) }}"><i class="fa fa-excel"></i>Print</a> --}}
                                        @endif
                                        @if (!$value->is_published)
                                            <a href="{{ route('pembelian.penerimaan', $value) }}"
                                                class="btn btn-sm btn-primary">
                                                Penerimaan Barang
                                            </a>
                                        @else
                                            <a href="{{ route('pembelian.penerimaan', $value) }}"
                                                class="btn btn-sm btn-info">
                                                Detail
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <a class=" btn-sm btn btn-success" href="{{ route('laporan.pembelian', $value->id) }}"><i class="fa fa-file-excel-o"></i> Export</a>
                                        <a href="{{ route('laporan.penerimaan', [$value->id, 'po']) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-file-excel-o"></i> Export Penerimaan PO
                                        </a>
                                        <a href="{{ route('laporan.penerimaan', [$value->id, 'outlet']) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-file-excel-o"></i> Export Penerimaan Outlet
                                        </a>
                                    </td>
                                    {{-- <td> --}}
                                    {{-- <form action="{{ route('pembelian.publish', $value) }}" method="POST"> --}}
                                    {{-- @csrf --}}
                                    {{-- <button class="btn btn-sm btn-primary" type="submit" @disabled($value->is_published)>Publish</button> --}}
                                    {{-- </form> --}}
                                    {{-- </td> --}}
                                </tr>
                            @endforeach
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
