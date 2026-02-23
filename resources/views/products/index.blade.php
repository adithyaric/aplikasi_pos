@inject('carbon', 'Carbon\Carbon')

@extends('layouts.master')

@section('title', 'Products')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Products
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('product.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive text-nowrap">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Code</td>
                                    {{-- <td>Nama Outlet</td> --}}
                                    <td>Nama</td>
                                    <td>Kategori</td>
                                    <td>Stock Owner</td>
                                    <td>Stock Reserverd</td>
                                    <td>Stock Warehouse</td>
                                    <td>Stock INBOUND</td>
                                    <td>Harga Beli</td>
                                    <td>Serialized</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->code }}</td>
                                        {{-- <td>{{ $value->outlet?->name }}</td> --}}
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->category->name }}</td>
                                        <td>{{ $value->ownerStocks()->sum('qty') }}</td>
                                        <td>{{ $value->stocks()->sum('qty_reserved') }}</td>
                                        <td>{{ $value->stocks()->sum('qty_available') }}</td>
                                        <td>{{ $value->stockPembelians()->sum('qty') }}</td>
                                        <td>@currency($value->harga_beli)</td>
                                        <td>{{ $value->is_serialized ? 'Yes' : 'No' }}</td>
                                        <td>
                                            <a class="btn btn-warning" href="{{ route('product.edit', $value->id) }}">Edit</a>
                                            <form action="{{ route('product.destroy', $value->id) }}" method="post" style="display: inline;">
                                                @method('delete')
                                                @csrf
                                                <button class="border-0 btn btn-danger"
                                                    onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
