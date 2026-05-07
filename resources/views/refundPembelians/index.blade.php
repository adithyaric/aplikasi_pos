@extends('layouts.master')

@section('title', 'Retur Pembelian')

@section('container')
    <section class="content-header">
        <h1>Data Retur Pembelian</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row align-items-center">

                            <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
                                <a href="{{ route('refundPembelian.create') }}" class="btn btn-sm bg-green">
                                    <i class="fa fa-plus"></i> Tambah Retur
                                </a>
                            </div>

                            <div class="col-md-8 col-sm-12">
                                <form method="GET" action="{{ route('laporan.retur-supplier') }}" id="exportForm">
                                    <div class="row g-0">
                                        <div class="col-md-3 col-6">
                                            <input type="date" name="tanggal_mulai" class="form-control input-sm" required>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <input type="date" name="tanggal_selesai" class="form-control input-sm" required>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <select name="type" id="typeSelect" class="form-control input-sm"
                                                onchange="updateFormAction(this.value)">
                                                <option value="" {{ $selectedType === null ? 'selected' : '' }}>Tampil Semua</option>
                                                <option value="gudang_ke_supplier" {{ $selectedType === 'gudang_ke_supplier' ? 'selected' : '' }}>Gudang ke Supplier</option>
                                                <option value="outlet_ke_gudang" {{ $selectedType === 'outlet_ke_gudang' ? 'selected' : '' }}>Retur Outlet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <button type="submit" id="exportButton" class="btn btn-success btn-sm w-100">
                                                <i class="fa fa-file-excel-o"></i> Export
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Retur</th>
                                    <th>Jenis</th>
                                    <th>Supplier / Outlet</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Operator</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refundPembelians as $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->code }}</td>
                                        <td>
                                            @if ($value->type === 'gudang_ke_supplier')
                                                <span class="label label-warning">Gudang → Supplier</span>
                                            @else
                                                <span class="label label-info">Outlet → Gudang</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $value->type === 'gudang_ke_supplier' ? $value->supplier->name ?? '-' : $value->outlet->name ?? '-' }}
                                        </td>
                                        <td>{{ $value->tanggal->format('d M Y') }}</td>
                                        <td>@currency($value->total)</td>
                                        <td>{{ $value->user->name ?? '-' }}</td>
                                        <td>
                                            @if ($value->status === 'retur')
                                                <span class="label label-danger">Retur</span>
                                            @else
                                                <span class="label label-success">Complete</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-default btn-xs"
                                                href="{{ route('refundPembelian.show', $value->id) }}">
                                                <i class="fa fa-eye"></i> Show
                                            </a>

                                            @if ($value->type === 'gudang_ke_supplier' && $value->status === 'retur')
                                                <a class="btn btn-success btn-xs"
                                                    href="{{ route('refundPembelian.terima.form', $value->id) }}">
                                                    <i class="fa fa-inbox"></i> Terima
                                                </a>
                                            @endif

                                            @if ($value->status !== 'complete')
                                                <form action="{{ route('refundPembelian.destroy', $value->id) }}"
                                                    method="post" style="display:inline">
                                                    @method('delete')
                                                    @csrf
                                                    <button class="btn btn-danger btn-xs"
                                                        onclick="return confirm('Hapus data ini?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($value->type === 'gudang_ke_supplier')
                                                <a href="{{ route('laporan.retur-pembelian.single', $value->id) }}"
                                                    class="btn btn-success btn-xs" target="_blank">
                                                    <i class="fa fa-file-excel-o"></i> Export XLSX
                                                </a>
                                                <a href="{{ route('laporan.pdf.retur-pembelian-single', $value->id) }}"
                                                    class="btn btn-danger btn-xs" target="_blank">
                                                    <i class="fa fa-file-pdf-o"></i> Export PDF
                                                </a>
                                            @else
                                                <a href="{{ route('laporan.retur-outlet.single', $value->id) }}"
                                                    class="btn btn-success btn-xs" target="_blank">
                                                    <i class="fa fa-file-excel-o"></i> Export XLSX
                                                </a>
                                                <a href="{{ route('laporan.pdf.retur-outlet-single', $value->id) }}"
                                                    class="btn btn-danger btn-xs" target="_blank">
                                                    <i class="fa fa-file-pdf-o"></i> Export PDF
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
@section('page-script')
    <script>
        // function setExportAction(type) {
        //     const form = document.getElementById('exportForm');
        //     const button = document.getElementById('exportButton');
        //     if (type === 'gudang_ke_supplier') {
        //         form.action = "{{ route('laporan.retur-supplier') }}";
        //         button.className = 'btn btn-warning btn-sm w-100';
        //     } else {
        //         form.action = "{{ route('laporan.retur-outlet') }}";
        //         button.className = 'btn btn-info btn-sm w-100';
        //     }
        // }

        function updateFormAction(type) {
            // setExportAction(type);

            const url = new URL(window.location.href);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            window.location.href = url.toString();
        }

        // const currentType = "{{ $selectedType ?? '' }}";
        // setExportAction(currentType || 'gudang_ke_supplier');
    </script>
@endsection
