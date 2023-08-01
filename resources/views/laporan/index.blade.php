@extends('layouts.master')

@section('title', 'ExportData')

@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Export Data
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="box">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#exportPembelian">
                        <i class="fa fa-print"></i>
                        Export Laporan Pembelian
                    </button>
                </div><!-- /.box -->
            </div><!-- /.col -->
            <div class="col-md-12 col-xs-12">
                <div class="box">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#exportPenjualan">
                        <i class="fa fa-print"></i>
                        Export Laporan Penjualan
                    </button>
                </div><!-- /.box -->
            </div><!-- /.col -->
            <div class="col-md-12 col-xs-12">
                <div class="box">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#exportPenjualanKasir">
                        <i class="fa fa-print"></i>
                        Export Laporan Penjualan By Kasir
                    </button>
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
        <!--Modals-->
        <div id="exportPembelian" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Export Laporan Pembelian</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('laporan.pembelian') }}" method="GET">
                        <div class="modal-body">
                            <p>Pilih Hari</p>
                            <div class="form-group">
                                <input type="date" placeholder="Pilih Tanggal" name="hari" class="form-control"
                                    value="{{ old('hari') }}" />
                            </div>
                            <hr />
                            <p>Pilih Tanggal</p>
                            <div class="form-group">
                                <input type="text" placeholder="Pilih Tanggal" name="tanggal"
                                    class="form-control tanggal" value="{{ old('tanggal') }}" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Export</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="exportPenjualan" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Export Laporan Penjualan</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('laporan.penjualan') }}" method="GET">
                        <div class="modal-body">
                            <p>Pilih Outlet</p>
                            <div class="form-group">
                                <select required class="form-control select2" name="outlet_id"
                                    data-placeholder="Pilih Outlet" style="width: 100%;">
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}"
                                            {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <hr />
                            <p>Pilih Hari</p>
                            <div class="form-group">
                                <input type="date" placeholder="Pilih Tanggal" name="hari" class="form-control"
                                    value="{{ old('hari') }}" />
                            </div>
                            <p>Pilih Tanggal</p>
                            <div class="form-group">
                                <input type="text" placeholder="Pilih Tanggal" name="tanggal"
                                    class="form-control tanggal" value="{{ old('tanggal') }}" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Export</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="exportPenjualanKasir" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Export Laporan Penjualan By Kasir</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('laporan.penjualan-kasir') }}" method="GET">
                        <div class="modal-body">
                            <p>Pilih Kasir</p>
                            <div class="form-group">
                                <select required class="form-control select2" name="kasir_id"
                                    data-placeholder="Pilih Kasir" style="width: 100%;">
                                    @foreach ($cashiers as $cashier)
                                        <option value="{{ $cashier->id }}"
                                            {{ old('kasir_id') == $cashier->id ? 'selected' : '' }}>
                                            {{ $cashier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p>Pilih Outlet</p>
                            <div class="form-group">
                                <select required class="form-control select2" name="outlet_id"
                                    data-placeholder="Pilih Outlet" style="width: 100%;">
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}"
                                            {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <hr />
                            <p>Pilih Hari</p>
                            <div class="form-group">
                                <input type="date" placeholder="Pilih Tanggal" name="hari" class="form-control"
                                    value="{{ old('hari') }}" />
                            </div>
                            <p>Pilih Tanggal</p>
                            <div class="form-group">
                                <input type="text" placeholder="Pilih Tanggal" name="tanggal"
                                    class="form-control tanggal" value="{{ old('tanggal') }}" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Export</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--End Modal-->
    </section><!-- /.content -->
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            var startDate = new Date();
            var endDate = new Date();

            $('.tanggal').daterangepicker({
                timePicker: true,
                timePickerIncrement: 30,
                format: 'YYYY-MM-DD H:mm',
                startDate: startDate,
                endDate: endDate
            });
        });
    </script>
@endsection
