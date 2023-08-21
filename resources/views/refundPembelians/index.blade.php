@extends('layouts.master')

@section('title', 'Refund/Return Pembelian')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Refund/Return Pembelian
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('refundPembelian.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama</td>
                                    <td>Nama Operator</td>
                                    <td>Total</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($refundPembelians as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->code }}</td>
                                    <td>{{ $value->user->name }}</td>
                                    <td>@currency($value->total)</td>
                                    <td>
                                        <a class="btn btn-info" href="{{ route('refundPembelian.show', $value->id) }}">Show</a>
                                        <a class="btn btn-warning" href="{{ route('refundPembelian.edit', $value->id) }}">Edit</a>
                                        <form action="{{ route('refundPembelian.destroy', $value->id) }}" method="post"
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
