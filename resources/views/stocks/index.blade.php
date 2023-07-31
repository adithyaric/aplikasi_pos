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
                        @foreach ($grouped_stocks as $product_id => $stock_group)
                            <p>
                                <b>{{ $stock_group->first()->product->name }}</b>
                            </p>
                            <table id="" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>No</td>
                                        <td>Harga Beli</td>
                                        <td>Qty</td>
                                        <td>Created</td>
                                        <td>Expired</td>
                                    </tr>
                                </thead>
                                @foreach ($stock_group as $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>@currency($value->harga_beli)</td>
                                        <td>{{ $value->qty }}</td>
                                        <td>{{ $value->created_at->format('h:i a / d-M-Y') }}</td>
                                        <td>{{ $value->expired_at->format('h:i a / d-M-Y') }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{ $stock_group->sum('qty') }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </table>
                            <hr />
                        @endforeach
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
