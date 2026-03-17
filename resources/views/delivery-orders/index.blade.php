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
                                        <a class="btn-sm btn btn-info" href="{{ route('delivery-orders.show', $value->id) }}">Detail</a>
                                        <a class=" btn-sm btn btn-success" href="{{ route('laporan.delivery-order', $value->id) }}"><i class="fa fa-file-excel-o"></i> Export</a>
                                        @if ($value->status == 'draft' || $value->status == 'sent')
                                            <button class="btn-sm btn btn-success" data-toggle="modal"
                                                data-target="#sendModal{{ $value->id }}">Send</button>

                                            <!-- Send Modal -->
                                            <div class="modal fade" id="sendModal{{ $value->id }}" tabindex="-1"
                                                role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('delivery-orders.send', $value->id) }}"
                                                            method="post" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">Send Delivery Order</h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Upload Dispatch Photo (Optional)</label>
                                                                    <input type="file" name="photo"
                                                                        class="form-control" accept="image/*">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-success"
                                                                    onclick="return confirm('Send this delivery order?')">Confirm
                                                                    Send</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
