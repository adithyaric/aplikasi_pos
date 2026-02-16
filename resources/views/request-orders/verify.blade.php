@extends('layouts.master')

@section('title', 'Verifikasi Request Order')

@section('container')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Verifikasi Request Order: {{ $requestOrder->code }}</h3>
                    </div>

                    <form action="{{ route('request-orders.process-verification', $requestOrder) }}" method="POST">
                        @csrf
                        <div class="box-body">
                            {{-- Info Request --}}
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px;">Kode Request</th>
                                    <td>{{ $requestOrder->code }}</td>
                                </tr>
                                <tr>
                                    <th>Owner (Outlet)</th>
                                    <td>{{ $requestOrder->owner->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Requested By</th>
                                    <td>{{ $requestOrder->requestedBy->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Request</th>
                                    <td>{{ $requestOrder->request_date->format('d-m-Y') }}</td>
                                </tr>
                                @if ($requestOrder->notes)
                                    <tr>
                                        <th>Catatan Umum</th>
                                        <td>{{ $requestOrder->notes }}</td>
                                    </tr>
                                @endif
                            </table>

                            <hr>
                            <h4>Item yang diminta</h4>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>Produk</td>
                                        <td>Qty Request</td>
                                        <td>Qty Approved</td>
                                        <td>Status</td>
                                        <td>Catatan Item</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestOrder->items as $index => $item)
                                        <tr>
                                            <td>
                                                {{ $item->product->name }}
                                                <input type="hidden" name="items[{{ $index }}][id]"
                                                    value="{{ $item->id }}">
                                            </td>
                                            <td>{{ $item->qty_requested }}</td>
                                            <td>
                                                <input type="number" class="form-control qty-approved"
                                                    name="items[{{ $index }}][qty_approved]"
                                                    value="{{ old('items.' . $index . '.qty_approved', $item->qty_approved ?? $item->qty_requested) }}"
                                                    min="0" max="{{ $item->qty_requested }}" step="1"
                                                    required>
                                            </td>
                                            <td>
                                                <select class="form-control item-status"
                                                    name="items[{{ $index }}][item_status]" required>
                                                    <option value="approved"
                                                        {{ old('items.' . $index . '.item_status', $item->item_status) == 'approved' ? 'selected' : '' }}>
                                                        Approved</option>
                                                    <option value="partial"
                                                        {{ old('items.' . $index . '.item_status', $item->item_status) == 'partial' ? 'selected' : '' }}>
                                                        Partial</option>
                                                    <option value="rejected"
                                                        {{ old('items.' . $index . '.item_status', $item->item_status) == 'rejected' ? 'selected' : '' }}>
                                                        Rejected</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control"
                                                    name="items[{{ $index }}][notes]"
                                                    value="{{ old('items.' . $index . '.notes', $item->notes) }}"
                                                    placeholder="Catatan item (opsional)">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="form-group">
                                <label for="verification_notes">Catatan Verifikasi (opsional)</label>
                                <textarea class="form-control" name="verification_notes" rows="3" placeholder="Catatan verifikasi...">{{ old('verification_notes', $requestOrder?->verification_notes) }}</textarea>
                            </div>
                            @if ($requestOrder->status == 'pending')
                                <button type="submit" class="btn btn-primary">Proses Verifikasi</button>
                            @endif
                        </div>
                    </form>
                    <div class="box-footer">
                        @if (($requestOrder->status == 'approved' || $requestOrder->status == 'partial') && !isset($requestOrder->pickingList))
                            <form action="{{ route('picking-lists.generate', $requestOrder->id) }}" method="post">
                                @csrf
                                <button class="btn btn-primary">
                                    <i class="fa fa-list"></i> Generate Picking List
                                </button>
                            </form>
                        <hr>
                        @endif
                        <a href="{{ route('request-orders.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
@endsection
