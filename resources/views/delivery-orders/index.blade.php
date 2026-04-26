@extends('layouts.master')
@section('title', 'Delivery Orders')
@section('container')
    <section class="content-header">
        <h1>DELIVERY ORDERS (OUTBOUND)</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Kode DO</td>
                                    <td>Request Order</td>
                                    <td>Owner/Outlet</td>
                                    <td>Delivery Date</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($deliveryOrders as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->code }}</td>
                                    <td>{{ $value->requestOrder->code }}</td>
                                    <td>{{ $value->owner->name }}</td>
                                    <td>{{ $value->delivery_date->format('d-m-Y') }}</td>
                                    <td>
                                        @if ($value->status == 'draft')
                                            <span class="label label-default">Draft</span>
                                        @elseif ($value->status == 'sent')
                                            <span class="label label-info">Sent</span>
                                        @elseif ($value->status == 'delivered')
                                            <span class="label label-success">Delivered</span>
                                        @elseif ($value->status == 'completed')
                                            <span class="label label-primary">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn-xs btn btn-default" href="{{ route('delivery-orders.show', $value->id) }}"><i class="fa fa-eye"></i> Detail</a>
                                        @if ($value->status == 'draft' || $value->status == 'sent')
                                            <button class="btn-xs btn btn-success" data-toggle="modal"
                                                data-target="#sendModal{{ $value->id }}">Send</button>

                                            @include('delivery-orders._send-modal', ['do' => $value])
                                        @endif
                                        <a class=" btn-xs btn btn-success" href="{{ route('laporan.delivery-order', $value->id) }}"><i class="fa fa-file-excel-o"></i> Export</a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
