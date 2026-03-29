@extends('layouts.master')

@section('title', 'Supplier')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Supplier
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('supplier.create') }}" class="btn btn-sm bg-green">Tambah</a>
                        <a href="{{ route('supplier.export') }}" class="btn btn-sm bg-light-blue">
                            <i class="fa fa-download"></i> Export
                        </a>
                        <a href="{{ route('supplier.export.template') }}" class="btn btn-sm bg-gray">
                            <i class="fa fa-file-excel-o"></i> Template
                        </a>
                        <button class="btn btn-sm bg-yellow" data-toggle="modal" data-target="#modalImport">
                            <i class="fa fa-upload"></i> Import
                        </button>
                    </div><!-- /.box-header -->

                    {{-- Modal Import --}}
                    <div class="modal fade" id="modalImport" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Import Supplier</h4>
                                </div>
                                <form action="{{ route('supplier.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>File Excel</label>
                                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                                            <p class="help-block">
                                                Download <a href="{{ route('supplier.export.template') }}">template</a> terlebih dahulu.
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
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Kode</td>
                                    <td>PIC</td>
                                    <td>Nama</td>
                                    <td>Alamat</td>
                                    <td>Nomor Telp</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($suppliers as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->kode_supplier }}</td>
                                    <td>{{ $value->pic_supplier }}</td>
                                    <td>{{ $value->name }}</td>
                                    <td>{{ $value->alamat }}</td>
                                    <td>{{ $value->no_telp }}</td>
                                    <td>
                                        <a class="btn btn-warning" href="{{ route('supplier.edit', $value->id) }}">Edit</a>
                                        <form action="{{ route('supplier.destroy', $value->id) }}" method="post"
                                            style="display: inline;">
                                            @method('delete')
                                            @csrf
                                            <button class="border-0 btn btn-danger"
                                                onclick="return confirm('Are you sure?')">Hapus</button>
                                        </form>
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
