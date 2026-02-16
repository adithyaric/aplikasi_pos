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
                                        <a class="btn-sm btn btn-info"
                                            href="{{ route('delivery-orders.show', $value->id) }}">Detail</a>
                                        @if ($value->status == 'draft')
                                            <form action="{{ route('delivery-orders.send', $value->id) }}" method="post"
                                                style="display: inline;">
                                                @csrf
                                                <button class="btn-sm btn btn-success"
                                                    onclick="return confirm('Send this delivery order?')">Send</button>
                                            </form>
                                        @elseif ($value->status == 'sent')
                                            <button class="btn-sm btn btn-warning" data-toggle="modal"
                                                data-target="#receiveModal{{ $value->id }}">Receive</button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Receive Modal -->
                                <div class="modal fade" id="receiveModal{{ $value->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('delivery-orders.receive', $value->id) }}" method="post"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <button type="button" class="close"
                                                        data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Receive Delivery</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Upload Photo Proof (Optional)</label>
                                                        <input type="file" name="photo" class="form-control"
                                                            accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default"
                                                        data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success">Confirm Received</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
