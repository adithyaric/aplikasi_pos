@extends('layouts.master')
@section('title', 'Picking Interface')
@section('container')
    <section class="content-header">
        <h1>Picking: {{ $pickingList->code }}</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <h3 class="box-title">Scan & Pick Items</h3>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('picking-lists.bulk-update', $pickingList->id) }}" method="POST">
                            @csrf
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Location</th>
                                            <th>SKU</th>
                                            <th>Qty to Pick</th>
                                            <th>Qty Picked</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pickingList->items as $item)
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ $item->location ?? $item->product?->lokasi }}</td>
                                                <td>{{ $item->sku ?? '-' }}</td>
                                                <td>{{ $item->qty_to_pick }}</td>
                                                <td>
                                                    <input type="number" name="items[{{ $item->id }}][qty_picked]"
                                                        class="form-control" value="{{ $item->qty_picked }}" min="0"
                                                        max="{{ $item->qty_to_pick }}" style="width: 80px;">
                                                </td>
                                                <td>
                                                    @if ($item->is_picked)
                                                        <span class="label label-success">✓ PICKED</span>
                                                    @else
                                                        <span class="label label-warning">PENDING</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="4"></td>
                                            <td>
                                                <button type="submit" class="btn btn-primary">Bulk Update</button>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer text-center">
                        <form action="{{ route('picking-lists.complete', $pickingList->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> Complete Picking
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
