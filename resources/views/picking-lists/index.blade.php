@extends('layouts.master')
@section('title', 'Picking Lists')
@section('container')
    <section class="content-header">
        <h1>PICKING & PACKING</h1>
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
                                    <td>Kode Picking</td>
                                    <td>Request Order</td>
                                    <td>Owner</td>
                                    <td>Picker</td>
                                    <td>Status</td>
                                    <td>Total Items</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($pickingLists as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->code }}</td>
                                    <td>{{ $value->requestOrder->code }}</td>
                                    <td>{{ $value->requestOrder->owner->name }}</td>
                                    <td>{{ $value->picker->name ?? '-' }}</td>
                                    <td>
                                        @if ($value->status == 'draft')
                                            <span class="label label-default">Draft</span>
                                        @elseif ($value->status == 'in_progress')
                                            <span class="label label-warning">In Progress</span>
                                        @elseif ($value->status == 'completed')
                                            <span class="label label-success">Completed</span>
                                        @endif
                                    </td>
                                    <td>{{ $value->items->count() }} items</td>
                                    <td>
                                        <a class=" btn-sm btn btn-success" href="{{ route('laporan.pickinglist', $value->id) }}"><i class="fa fa-file-excel-o"></i> Export</a>
                                        <a class="btn-sm btn btn-info" href="{{ route('picking-lists.show', $value->id) }}">Detail</a>
                                        @if (!isset($value->deliveryOrder))
                                            @if ($value->status == 'draft')
                                                <form action="{{ route('picking-lists.start', $value->id) }}" method="post"
                                                    style="display: inline;">
                                                    @csrf
                                                    <button class="btn-sm btn btn-success">Start Picking</button>
                                                </form>
                                            @elseif ($value->status == 'in_progress')
                                                <a class="btn-sm btn btn-warning"
                                                    href="{{ route('picking-lists.pick', $value->id) }}">Continue</a>
                                            @elseif ($value->status == 'completed')
                                                <form action="{{ route('delivery-orders.generate', $value->id) }}"
                                                    method="post" style="display: inline;">
                                                    @csrf
                                                    <button class="btn-sm btn btn-primary">Generate DO & Send to outlet</button>
                                                </form>
                                            @endif
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
