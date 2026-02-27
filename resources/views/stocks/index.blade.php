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
                                    <td>Code</td>
                                    {{-- <td>Nama Outlet</td> --}}
                                    <td>Nama Product</td>
                                    <td>Harga Beli</td>
                                    <td>Qty Reserved</td>
                                    <td>Qty Warehouse</td>
                                    <td>Created</td>
                                    <td>Expired</td>
                                    <td>Status</td>
                                    <td>Sku</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stocks as $stock)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stock->serial_number ?? $stock->product->code }}</td>
                                        {{-- <td>{{ $stock->pembelian->outlet?->name }}</td> --}}
                                        <td>{{ $stock->product->name }}</td>
                                        <td>@currency($stock->harga_beli)</td>
                                        <td>{{ $stock->qty_reserved }}</td>
                                        <td>{{ $stock->qty_available }}</td>
                                        <td>{{ $stock->created_at?->format('h:i a / d-M-Y') }}</td>
                                        <td>{{ $stock->expired_at?->format('h:i a / d-M-Y') }}</td>
                                        <td>{{ $stock->status }}</td>
                                        <td>{{ $stock->sku }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <hr />
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
