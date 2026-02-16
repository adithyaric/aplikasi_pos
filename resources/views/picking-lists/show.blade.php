@extends('layouts.master')
@section('title', 'Picking List Detail')
@section('container')
    <section class="content-header">
        <h1>Picking List: {{ $pickingList->code }}</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informasi</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Request Order</th>
                                <td>{{ $pickingList->requestOrder->code }}</td>
                            </tr>
                            <tr>
                                <th>Owner/Outlet</th>
                                <td>{{ $pickingList->requestOrder->owner->name }}</td>
                            </tr>
                            <tr>
                                <th>Picker</th>
                                <td>{{ $pickingList->picker->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($pickingList->status == 'draft')
                                        <span class="label label-default">Draft</span>
                                    @elseif ($pickingList->status == 'in_progress')
                                        <span class="label label-warning">In Progress</span>
                                    @elseif ($pickingList->status == 'completed')
                                        <span class="label label-success">Completed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Started At</th>
                                <td>{{ $pickingList->started_at ? $pickingList->started_at->format('d-m-Y H:i') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Completed At</th>
                                <td>{{ $pickingList->completed_at ? $pickingList->completed_at->format('d-m-Y H:i') : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Items to Pick</h3>
                    </div>
                    <div class="box-body table-responsive text-nowrap">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Batch</th>
                                    <th>Qty to Pick</th>
                                    <th>Qty Picked</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pickingList->items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->location ?? '-' }}</td>
                                        <td>{{ $item->batch_number ?? '-' }}</td>
                                        <td>{{ $item->qty_to_pick }}</td>
                                        <td>{{ $item->qty_picked }}</td>
                                        <td>
                                            @if ($item->is_picked)
                                                <span class="label label-success">✓ Picked</span>
                                            @else
                                                <span class="label label-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
