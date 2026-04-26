@inject('carbon', 'Carbon\Carbon')

@extends('layouts.master')

@section('title', 'Products')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Products
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('product.create') }}" class="btn btn-sm bg-green">Tambah</a>
                        <a href="{{ route('product.export') }}" class="btn btn-sm bg-light-blue">
                            <i class="fa fa-download"></i> Export
                        </a>
                        <a href="{{ route('product.export.template') }}" class="btn btn-sm bg-gray">
                            <i class="fa fa-file-excel-o"></i> Template
                        </a>
                        <button class="btn btn-sm bg-yellow" data-toggle="modal" data-target="#modalImport">
                            <i class="fa fa-upload"></i> Import
                        </button>

                        //TODO add import export update min_stock only
                        //barcode, nama, min_stock
                    </div><!-- /.box-header -->

                    {{-- Modal Import --}}
                    <div class="modal fade" id="modalImport" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Import Product</h4>
                                </div>
                                <form action="{{ route('product.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>File Excel</label>
                                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                                            <p class="help-block">
                                                Download <a href="{{ route('product.export.template') }}">template</a> terlebih dahulu.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-upload"></i> Import
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="box-body table-responsive text-nowrap">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Barcode</td>
                                    {{-- <td>Nama Outlet</td> --}}
                                    <td>Nama</td>
                                    <td>Kategori</td>
                                    <td>Stock Owner</td>
                                    <td>Stock Reserverd</td>
                                    <td>Stock Warehouse</td>
                                    <td>Stock INBOUND</td>
                                    <td>Stock Minimum</td>
                                    <td>Harga Beli</td>
                                    {{-- <td>Harga Jual</td> --}}
                                    <td>Notif</td>
                                    {{-- <td>Serialized</td> --}}
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->code }}</td>
                                        {{-- <td>{{ $value->outlet?->name }}</td> --}}
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->category->name }}</td>
                                        <td>{{ $value->ownerStocks()->sum('qty') }}</td>
                                        <td>{{ $value->stocks()->sum('qty_reserved') }}</td>
                                        <td>{{ $value->stocks()->sum('qty_available') }}</td>
                                        <td>{{ $value->stockPembelians()->sum('qty') }}</td>
                                        <td>{{ $value->min_stock }}</td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-info btn-price-history"
                                                data-toggle="modal" data-target="#priceHistoryModal"
                                                data-id="{{ $value->id }}">
                                                @currency($value->harga_beli)
                                            </button>
                                        </td>
                                        {{-- <td>@currency($value->harga_jual)</td> --}}
                                        <td>
                                            @if ($value->stocks()->sum('qty_available') < $value->min_stock)
                                            habis, stock tinggal {{ $value->stocks()->sum('qty_available') }}
                                            @else
                                            aman
                                            @endif
                                        </td>
                                        {{-- <td>{{ $value->is_serialized ? 'Yes' : 'No' }}</td> --}}
                                        <td>
                                            <a class="btn btn-warning"
                                                href="{{ route('product.edit', $value->id) }}">Edit</a>
                                            <form action="{{ route('product.destroy', $value->id) }}" method="post"
                                                style="display: inline;">
                                                @method('delete')
                                                @csrf
                                                <button class="border-0 btn btn-danger"
                                                    onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Price History Modal -->
                        <div class="modal fade" id="priceHistoryModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Price History (Harga Beli)</h4>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>User</th>
                                                    <th>Change</th>
                                                </tr>
                                            </thead>
                                            <tbody id="priceHistoryBody">
                                                <tr>
                                                    <td colspan="3" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#priceHistoryModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var modal = $(this);
                modal.find('#priceHistoryBody').html(
                    '<tr><td colspan="3" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: '/product/' + id + '/price-history',
                    method: 'GET',
                    success: function(res) {
                        var rows = '';
                        if (res.data && res.data.length) {
                            res.data.forEach(function(item) {
                                var change = item.event === 'created' ?
                                    'Created → ' + Number(item.new).toLocaleString() :
                                    Number(item.old).toLocaleString() + ' → ' + Number(
                                        item.new).toLocaleString();
                                rows += '<tr><td>' + item.date + '</td><td>' + item
                                    .user + '</td><td>' + change + '</td></tr>';
                            });
                        } else {
                            rows =
                                '<tr><td colspan="3" class="text-center">No changes found.</td></tr>';
                        }
                        modal.find('#priceHistoryBody').html(rows);
                    },
                    error: function() {
                        modal.find('#priceHistoryBody').html(
                            '<tr><td colspan="3" class="text-center text-danger">Error loading data.</td></tr>'
                            );
                    }
                });
            });
        });
    </script>
@endsection
