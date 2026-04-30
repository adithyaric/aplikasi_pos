@extends('layouts.master')

@section('title', 'Purchase Order')

@section('container')
    <section class="content-header">
        <h1>Purchase Order <small>Gudang → Supplier</small></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('pembelian.create') }}" class="btn btn-md bg-green">
                            <i class="fa fa-plus"></i> Buat PO Baru
                        </a>
                        {{-- <a href="{{ route('refundPembelian.index') }}" class="btn btn-md bg-green"> --}}
                            {{-- <i class="fa fa-refresh"></i> Refund PO --}}
                        {{-- </a> --}}
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Kode PO</th>
                                    <th>Supplier</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th width="120">Status PO</th>
                                    <th width="120">Status Bayar</th>
                                    <th width="200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pembelians as $value)
                                    @php
                                        $payStatus = $value->pembelianTransaction?->status ?? 'unpaid';
                                        $payBadge = match ($payStatus) {
                                            'paid' => 'success',
                                            'partial' => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $value->code }}</strong></td>
                                        <td>{{ $value->supplier?->name }}</td>
                                        <td>
                                            <ul class="list-unstyled" style="margin:0">
                                                @foreach ($value->pembelianProducts as $item)
                                                    <li>
                                                        <small>
                                                            {{ $item->product?->name }} × {{ $item->qty }}
                                                            @php $k = $item->product?->konversiDisplay($item->qty); @endphp
                                                            @if($k && $k !== '-')
                                                                <span class="label label-info">{{ $k }}</span>
                                                            @endif
                                                        </small>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>@currency($value->total)</td>
                                        <td>
                                            @if ($value->is_published)
                                                <span class="label label-success">PUBLISHED</span>
                                            @else
                                                <span class="label label-default">DRAFT</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label label-{{ $payBadge }}">
                                                {{ strtoupper($payStatus) }}
                                            </span>
                                            @if ($value->pembelianTransaction?->amount > 0)
                                                <br><small>@currency($value->pembelianTransaction?->amount)</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Bayar --}}
                                            <a href="{{ route('pembelian.pembayaran.edit', $value->id) }}"
                                                class="btn btn-xs btn-default"
                                                title="Pembayaran">
                                                <i class="fa fa-credit-card"></i> Pembayaran
                                            </a>

                                            @if (!$value->is_published)
                                                <a href="{{ route('pembelian.edit', $value->id) }}"
                                                    class="btn btn-xs btn-warning" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <form action="{{ route('pembelian.destroy', $value->id) }}" method="post"
                                                    style="display:inline">
                                                    @method('delete')
                                                    @csrf
                                                    <button class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Hapus PO {{ $value->code }}?')"
                                                        title="Hapus">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Export PO --}}
                                            <a href="{{ route('laporan.pembelian', $value->id) }}"
                                                class="btn btn-xs btn-success" title="Export PO">
                                                <i class="fa fa-file-excel-o"></i> PO
                                            </a>
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
