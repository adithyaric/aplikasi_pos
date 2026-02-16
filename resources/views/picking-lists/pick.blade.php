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
                        @foreach ($pickingList->items as $item)
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4>
                                        {{ $item->product->name }}
                                        @if ($item->is_picked)
                                            <span class="label label-success pull-right">✓ PICKED</span>
                                        @else
                                            <span class="label label-warning pull-right">PENDING</span>
                                        @endif
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p><strong>Location:</strong> {{ $item->location ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Batch:</strong> {{ $item->batch_number ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Qty to Pick:</strong> {{ $item->qty_to_pick }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <form action="{{ route('picking-list-items.update', $item->id) }}"
                                                method="post" class="form-inline">
                                                @csrf
                                                @method('PATCH')
                                                <div class="form-group">
                                                    <label>Qty Picked:</label>
                                                    <input type="number" name="qty_picked" class="form-control"
                                                        value="{{ $item->qty_picked }}" min="0"
                                                        max="{{ $item->qty_to_pick }}" style="width: 80px;">
                                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="text-center" style="margin-top: 20px;">
                            <form action="{{ route('picking-lists.complete', $pickingList->id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-check"></i> Complete Picking
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
