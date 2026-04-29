@extends('layouts.master')
@section('title', 'Detail Request Order')
@section('container')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Detail Request Order - {{ $requestOrder->code }}</h3>
                    </div>

                    <div class="box-body">
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
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="label label-{{ $requestOrder->status == 'approved' ? 'success' : ($requestOrder->status == 'partial' ? 'warning' : ($requestOrder->status == 'rejected' ? 'danger' : 'default')) }}">
                                        {{ ucfirst($requestOrder->status) }}
                                    </span>
                                </td>
                            </tr>
                            @if ($requestOrder->verified_by)
                                <tr>
                                    <th>Diverifikasi Oleh</th>
                                    <td>{{ $requestOrder->verifiedBy->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Verifikasi</th>
                                    <td>{{ $requestOrder->verified_date?->format('d-m-Y H:i') ?? '-' }}</td>
                                </tr>
                                @if ($requestOrder->verification_notes)
                                    <tr>
                                        <th>Catatan Verifikasi</th>
                                        <td>{{ $requestOrder->verification_notes }}</td>
                                    </tr>
                                @endif
                            @endif
                        </table>

                        <hr>
                        <h4>Items</h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>SKU</th>
                                    <th>Qty Requested</th>
                                    <th>Qty Approved</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requestOrder->items as $item)
                                    @php
                                        $stock = $item->stock;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td><small class="text-muted">{{ $stock->sku ?? 'N/A' }}</small></td>
                                        <td>
                                            {{ $item->qty_requested }}
                                            @if ($item->product->konversi_qty && $item->product->satuan_besar)
                                                <br><small class="text-muted">{{ $item->product->konversiDisplay($item->qty_requested) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->qty_approved }}</td>
                                        <td>
                                            @if ($item->item_status === 'approved')
                                                <span class="label label-success">Approved</span>
                                            @elseif ($item->item_status === 'partial')
                                                <span class="label label-warning">Partial</span>
                                            @elseif ($item->item_status === 'rejected')
                                                <span class="label label-danger">Rejected</span>
                                            @else
                                                <span class="label label-default">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if ($requestOrder->additionalNotes->isNotEmpty())
                            <hr>
                            <h4>Sample</h4>
                            <table class="table table-bordered table-condensed" style="max-width:600px">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Kategori</th>
                                        <th width="80" class="text-center">Qty</th>
                                        <th>Nama PJ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestOrder->additionalNotes as $i => $note)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $note->kategori }}</td>
                                            <td class="text-center">{{ $note->qty }}</td>
                                            <td>{{ $note->nama_pj ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('request-orders.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection