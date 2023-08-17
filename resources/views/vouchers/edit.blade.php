@extends('layouts.master')

@section('title', 'Edit Voucher')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Voucher</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('voucher.update', $voucher->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label for="">Nama Voucher</label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ old('name', $voucher->name) }}" placeholder="Masukkan Nama Voucher">
                                @error('name')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Kode Voucher</label>
                                <input type="text" class="form-control" name="code"
                                    value="{{ old('code', $voucher->code) }}" placeholder="Masukkan Kode Voucher">
                                @error('code')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Limit</label>
                                <input type="number" class="form-control" name="limit"
                                    value="{{ old('limit', $voucher->limit) }}" placeholder="Masukkan Limit">
                                @error('limit')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Tipe</label>
                                <select class="form-control select2" name="type" data-placeholder="Pilih Tipe"
                                    style="width: 100%;">
                                    <option value="" selected disabled>Pilih Tipe</option>
                                    <option @if ($voucher->type == 'nominal') selected @endif value="nominal">Nominal
                                    </option>
                                    <option @if ($voucher->type == 'percentage') selected @endif value="percentage">Percentage
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Nominal/Percentage</label>
                                <input type="text" class="form-control" name="value"
                                    value="{{ old('value', $voucher->value) }}" placeholder="Masukkan Nominal">
                                @error('value')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Minimal Pembelian</label>
                                <input type="number" class="form-control" name="min_purchase"
                                    value="{{ old('min_purchase', $voucher->min_purchase) }}"
                                    placeholder="Masukkan Minimal Pembelian">
                                @error('min_purchase')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Deskripsi</label>
                                <input type="text" class="form-control" name="desc"
                                    value="{{ old('desc', $voucher->desc) }}" placeholder="Masukkan Deskripsi">
                                @error('desc')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Rentang Waktu</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input type="text" name="daterange" id="daterange" class="form-control input-sm"
                                        value="{{ $defaultDateRange }}" />
                                </div>
                                <p class="help-block">Rentang waktu</p>
                            </div>
                            <div class="form-group">
                                <label>Digunakan Untuk</label>
                                <select class="form-control select2" id="jenis" name="jenis"
                                    data-placeholder="Pilih Jenis" style="width: 100%;">
                                    <option value="" selected disabled>Pilih Jenis</option>
                                    <option @if ($voucher->jenis == 'satuan') selected @endif value="satuan">Satuan</option>
                                    <option @if ($voucher->jenis == 'keseluruhan') selected @endif value="keseluruhan">
                                        Keseluruhan</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Product</label>
                                <select class="form-control select2" id="product" name="product_id"
                                    data-placeholder="Pilih Product" style="width: 100%;">
                                    <option value="" selected disabled>Pilih Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ old('product_id', $voucher->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <a href="{{ route('voucher.index') }}" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div><!-- /.box -->
            </div>
        </div>
    </section>
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            // Check the value of #jenis on page load
            if ($('#jenis').val() === 'satuan') {
                $('#product').prop('disabled', false);
            } else {
                $('#product').prop('disabled', true);
            }

            // Listen for changes on the "Jenis" select form
            $('#jenis').on('change', function() {
                // Check if the selected value is "Satuan"
                if ($(this).val() === 'satuan') {
                    // Enable the product select form
                    $('#product').prop('disabled', false);
                } else {
                    // Disable the product select form
                    $('#product').prop('disabled', true);
                }
            });
            var startDate = new Date();
            var endDate = new Date();

            $('#daterange').daterangepicker({
                timePicker: true,
                timePickerIncrement: 30,
                format: 'YYYY-MM-DD H:mm',
                startDate: startDate,
                endDate: endDate
            });
        });
    </script>
@endsection
