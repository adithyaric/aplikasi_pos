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
                        <a href="{{ route('refundPembelian.create') }}" class="btn btn-md bg-green">
                            <i class="fa fa-plus"></i> Tambah Retur
                        </a>
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
                                            <a class="btn btn-info btn-sm"
                                                href="{{ route('refundPembelian.show', $value->id) }}">
                                                <i class="fa fa-eye"></i> Show
                                            </a>

                                            @if ($value->type === 'gudang_ke_supplier' && $value->status === 'retur')
                                                <a class="btn btn-success btn-sm"
                                                    href="{{ route('refundPembelian.terima.form', $value->id) }}">
                                                    <i class="fa fa-inbox"></i> Terima
                                                </a>
                                            @endif

                                            @if ($value->status !== 'complete')
                                                <form action="{{ route('refundPembelian.destroy', $value->id) }}"
                                                    method="post" style="display:inline">
                                                    @method('delete')
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus data ini?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
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
