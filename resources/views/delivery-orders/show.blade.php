@extends('layouts.master')
@section('title', 'Delivery Order Detail')
@section('container')
    <section class="content-header">
        <h1>Delivery Order: {{ $deliveryOrder->code }}</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informasi Pengiriman</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Kode DO</th>
                                <td>{{ $deliveryOrder->code }}</td>
                            </tr>
                            <tr>
                                <th>Request Order</th>
                                <td>{{ $deliveryOrder->requestOrder->code }}</td>
                            </tr>
                            <tr>
                                <th>Owner/Outlet</th>
                                <td>{{ $deliveryOrder->owner->name }}</td>
                            </tr>
                            <tr>
                                <th>Prepared By</th>
                                <td>{{ $deliveryOrder->preparedBy->name }}</td>
                            </tr>
                            <tr>
                                <th>Delivery Date</th>
                                <td>{{ $deliveryOrder->delivery_date->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($deliveryOrder->status == 'draft')
                                        <span class="label label-default">Draft</span>
                                    @elseif ($deliveryOrder->status == 'sent')
                                        <span class="label label-info">Sent</span>
                                    @elseif ($deliveryOrder->status == 'delivered')
                                        <span class="label label-success">Delivered</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($deliveryOrder->received_date)
                                <tr>
                                    <th>Received Date</th>
                                    <td>{{ $deliveryOrder->received_date->format('d-m-Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Received By</th>
                                    <td>{{ $deliveryOrder->receivedBy->name ?? '-' }}</td>
                                </tr>
                            @endif
                            @if ($deliveryOrder->photo_path)
                                <tr>
                                    <th>Photo Proof</th>
                                    <td>
                                        <a href="{{ asset('storage/' . $deliveryOrder->photo_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $deliveryOrder->photo_path) }}"
                                                style="max-width: 200px;">
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Items</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Expired</th>
                                    <th>Qty</th>
                                    <th>HPP</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach ($deliveryOrder->items as $item)
                                    @php
                                        $subtotal = $item->qty * $item->hpp;
                                        $total += $subtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->batch_number ?? '-' }}</td>
                                        <td>{{ $item->expired_at ? $item->expired_at->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>Rp {{ number_format($item->hpp, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">TOTAL</th>
                                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('delivery-orders.index') }}" class="btn btn-default">Back</a>
                        @if ($deliveryOrder->status == 'draft')
                            <form action="{{ route('delivery-orders.send', $deliveryOrder->id) }}" method="post"
                                style="display: inline;">
                                @csrf
                                <button class="btn btn-success" onclick="return confirm('Send this delivery order?')">
                                    <i class="fa fa-truck"></i> Send to Outlet
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
