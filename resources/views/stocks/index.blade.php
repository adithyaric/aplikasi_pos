@extends('layouts.master')

@section('title', 'Stocks')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Stocks
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama</td>
                                    <td>Qty</td>
                                    <td>Harga Beli</td>
                                    <td>Created</td>
                                    <td>Expired</td>
                                </tr>
                            </thead>
                            @foreach ($stocks as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->product->name }}</td>
                                    <td>@currency($value->harga_beli)</td>
                                    <td>{{ $value->qty }}</td>
                                    <td>{{ $value->created_at->format('h:i a / d-M-Y') }}</td>
                                    <td>{{ $value->expired_at->format('h:i a / d-M-Y') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
