@extends('layouts.master')

@section('title', 'Penerimaan Barang (GR)')

@section('container')
    <section class="content-header">
        <h1>Penerimaan Barang <small>Goods Receipt</small></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            Pilih PO untuk melakukan input penerimaan barang dari supplier.
                        </p>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Kode PO</th>
                                    <th>Kode GR</th>
                                    <th>Supplier</th>
                                    <th>Items</th>
                                    <th>Total PO</th>
                                    <th width="130">Status Penerimaan</th>
                                    <th>Tgl Terima</th>
                                    <th>PIC</th>
                                    <th width="180">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pembelians as $value)
                                    @php
                                        $receiptStatus = $value->receipt_status ?? 'draft';
                                        $receiptBadge = match($receiptStatus) {
                                            'completed' => 'success',
                                            'validated' => 'info',
                                            default     => 'warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $value->code }}</strong></td>
                                        <td>{{ $value->code_gr ?? '-' }}</td>
                                        <td>{{ $value->supplier?->name }}</td>
                                        <td>
                                            <ul class="list-unstyled" style="margin:0">
                                                @foreach ($value->pembelianProducts as $item)
                                                    <li>
                                                        <small>{{ $item->product->name }}</small>
                                                        <span class="label label-default">{{ $item->qty }}</span>
                                                        @if($value->stocks->where('product_id', $item->product_id)->count())
                                                            <span class="label label-success">
                                                                {{-- ✓ {{ $value->stocks->where('product_id', $item->product_id)->sum('qty') }} diterima --}}
                                                                ✓ {{ $item->qty_diterima }} diterima
                                                            </span>
                                                        @else
                                                            <span class="label label-warning">Belum diterima</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>@currency($value->total)</td>
                                        <td>
                                            <span class="label label-{{ $receiptBadge }}">
                                                {{ strtoupper($receiptStatus) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $value->receipt_date ? \Carbon\Carbon::parse($value->receipt_date)->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td>{{ $value->receipt_pic ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('pembelian.penerimaan', $value) }}"
                                               class="btn btn-xs btn-{{ $receiptStatus === 'completed' ? 'default' : 'primary' }}">
                                                <i class="fa fa-{{ $receiptStatus === 'completed' ? 'eye' : 'edit' }}"></i>
                                                {{ $receiptStatus === 'completed' ? 'Detail' : 'Input GR' }}
                                            </a>
                                            @if($value->stocks->count())
                                                <a href="{{ route('laporan.penerimaan', [$value->id, 'po']) }}"
                                                   class="btn btn-xs btn-success" title="Export GR">
                                                    <i class="fa fa-file-excel-o"></i> GR
                                                </a>
                                                <a href="{{ route('laporan.pdf.penerimaan-single', $value->id) }}" class="btn btn-xs btn-danger" title="Export PDF GR">
                                                    <i class="fa fa-file-pdf-o"></i> GR
                                                </a>
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