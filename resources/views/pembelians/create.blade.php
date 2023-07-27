@extends('layouts.master')

@section('title', 'Tambah Pembelian')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tambah Pembelian</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label for="">Kode Pembelian</label>
                                <input type="text" class="form-control" name="code" value="{{ old('code') }}"
                                    placeholder="Masukkan Kode Pembelian">
                                @error('code')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Outlet</label>
                                <select class="form-control select2" name="outlet_id" data-placeholder="Pilih Outlet"
                                    style="width: 100%;">
                                    <option value="" selected disabled>Pilih Outlet</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}"
                                            {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('outlet_id')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Supplier</label>
                                <select class="form-control select2" name="supplier_id" data-placeholder="Pilih Supplier"
                                    style="width: 100%;">
                                    <option value="" selected disabled>Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <hr>
                            <div class="forn-group">
                                <div id="product-repeater">
                                    <div class="row">
                                        <div class="form-group col-sm-3">
                                            <label>Product</label>
                                            <select required class="form-control select2" name="product[0][product_id]"
                                                data-placeholder="Pilih Product" style="width: 100%;">
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Qty</label>
                                            <input type="text" required class="form-control" name="product[0][qty]">
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Expired</label>
                                            <input type="date" required class="form-control" name="product[0][expired]">
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Harga Beli</label>
                                            <input type="text" required class="form-control numeral-mask"
                                                name="product[0][harga_beli]">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="addBahanBaku()">Add </button>
                            </div>
                            <hr>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <a href="{{ route('pembelian.index') }}" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div><!-- /.box -->
            </div>
        </div>
    </section>
@endsection
@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        let productIndex = 0;

        function addBahanBaku() {
            productIndex++;
            let productTemplate = `
        <div class="row">
            <div class="form-group col-sm-3">
                <label>Product</label>
                <select required class="form-control select2" name="product[${productIndex}][product_id]"
                    data-placeholder="Pilih Product" style="width: 100%;">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-sm-3">
                <label>Qty</label>
                <input type="text" required class="form-control" name="product[${productIndex}][qty]">
            </div>
            <div class="form-group col-sm-3">
                <label>Expired</label>
                <input type="date" required class="form-control" name="product[${productIndex}][expired]">
            </div>
            <div class="form-group col-sm-3">
                <label>Harga Beli</label>
                <input type="text" required class="form-control numeral-mask" name="product[${productIndex}][harga_beli]">
            </div>
        </div>
    `;
            $('#product-repeater').append(productTemplate);
            $('#product-repeater .select2').last().select2();
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.numeral-mask').mask("#,##0", {
                reverse: true
            });
        });
    </script>
@endsection
