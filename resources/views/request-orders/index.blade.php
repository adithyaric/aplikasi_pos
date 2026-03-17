@extends('layouts.master')

@section('title', 'Request Orders')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            OUTLET REQUESTS STOCK
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('request-orders.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Kode Request</td>
                                    <td>Owner (Outlet)</td>
                                    <td>Requested By</td>
                                    <td>Tanggal Request</td>
                                    <td>Status</td>
                                    <td>Items</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($requests as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->code }}</td>
                                    <td>{{ $value->owner->name ?? '-' }}</td>
                                    <td>{{ $value->requestedBy->name ?? '-' }}</td>
                                    <td>{{ $value->request_date->format('d-m-Y') }}</td>
                                    <td>
                                        @if ($value->status == 'pending')
                                            <span class="label label-warning">Pending</span>
                                        @elseif ($value->status == 'verified')
                                            <span class="label label-success">Verified</span>
                                        @elseif ($value->status == 'cancelled')
                                            <span class="label label-danger">Cancelled</span>
                                        @else
                                            <span class="label label-default">{{ $value->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach ($value->items as $item)
                                                <li>
                                                    {{ $item->product->name ?? 'Produk' }}: {{ $item->qty_approved }} pcs
                                                    @if (!empty($item->notes))
                                                        <small>({{ $item->notes }})</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        {{-- @if ($value->status == 'pending') --}}
                                            {{-- <a class="btn-sm btn btn-warning" href="{{ route('request-orders.edit', $value->id) }}">Edit</a> --}}
                                            {{-- <form action="{{ route('request-orders.destroy', $value->id) }}" method="post" style="display: inline;"> --}}
                                                {{-- @method('delete') --}}
                                                {{-- @csrf --}}
                                                {{-- <button class="border-0 btn-sm btn btn-danger" onclick="return confirm('Are you sure?')">Hapus</button> --}}
                                            {{-- </form> --}}
                                        {{-- @else --}}
                                            <!-- optional print if needed -->
                                            {{-- <a class="btn-sm btn btn-primary" href="{{ route('request-orders.print', $value->id) }}">Print</a> --}}
                                        {{-- @endif --}}
                                        @if (($value->status == 'approved' || $value->status == 'partial') && !isset($value->pickingList))
                                            <form action="{{ route('picking-lists.generate', $value->id) }}" method="post">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fa fa-list"></i> Generate Picking List
                                                </button>
                                            </form>
                                            <br>
                                        @endif
                                        <a class=" btn-sm btn btn-success" href="{{ route('laporan.request-order', $value->id) }}"><i class="fa fa-file-excel-o"></i> Export</a>
                                        <a class="btn-sm btn btn-info" href="{{ route('request-orders.verify', $value->id) }}">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
