@extends('layouts.master')

@section('title', 'Penjualan')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Penjualan
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('penjualan.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Kode Invoice</td>
                                    <td>Customer</td>
                                    <td>kasir</td>
                                    <td>Detail</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($penjualan as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->code }}</td>
                                    <td>{{ $value->customer->name }}</td>
                                    <td>{{ $value->kasir->name }}</td>
                                    <td>
                                        <ul>
                                            @foreach ($value->items as $item)
                                                <li>
                                                    {{ $item->product->name }}. Banyak : {{ $item->qty }}. Sub total
                                                    @currency($item->qty * $item->price)
                                                </li>
                                            @endforeach
                                        </ul>
                                        Total : @currency($value->total + $value->discount)
                                        Diskon : @currency($value->discount)
                                        Grand Total : @currency($value->total)
                                    </td>
                                    <td>
                                        <a class="btn btn-info" href="{{ route('penjualan.show', $value->id) }}">Show</a>
                                        <form action="{{ route('penjualan.destroy', $value->id) }}" method="post"
                                            style="display: inline;">
                                            @method('delete')
                                            @csrf
                                            <button class="border-0 btn btn-danger"
                                                onclick="return confirm('Are you sure?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
