@extends('layouts.master')

@section('title', 'Tambah Product')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tambah Product</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row box-body">
                            <div class="col-md-6 form-group">
                                <label for="">Nama</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    placeholder="Masukkan Nama">
                                @error('name')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Barcode</label>
                                <input type="text" class="form-control" name="code" value="{{ old('code') }}"
                                    placeholder="Masukkan Barcode">
                                @error('code')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Warna</label>
                                <input type="text" class="form-control" name="warna" value="{{ old('warna') }}"
                                    placeholder="Masukkan Warna">
                                @error('warna')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Ukuran</label>
                                <input type="text" class="form-control" name="ukuran" value="{{ old('ukuran') }}"
                                    placeholder="Masukkan Ukuran">
                                @error('ukuran')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Brand</label>
                                <input type="text" class="form-control" name="brand" value="{{ old('brand') }}"
                                    placeholder="Masukkan Brand">
                                @error('brand')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Model</label>
                                <input type="text" class="form-control" name="model" value="{{ old('model') }}"
                                    placeholder="Masukkan Model">
                                @error('model')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Harga Beli</label>
                                <input type="text" class="form-control" name="harga_beli" value="{{ old('harga_beli', 0) }}"
                                    placeholder="Masukkan Harga Beli">
                                @error('harga_beli')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Satuan -->
                            <div class="col-md-6 form-group">
                                <label for="">Satuan</label>
                                <input type="text" class="form-control" name="satuan"
                                    value="{{ old('satuan', $product->satuan ?? '') }}" placeholder="Contoh: Pcs, Box, Kg">
                                @error('satuan')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Satuan Besar (Konversi) -->
                            <div class="col-md-6 form-group">
                                <label for="">Satuan Besar <small class="text-muted">(Opsional)</small></label>
                                <input type="text" class="form-control" name="satuan_besar"
                                    value="{{ old('satuan_besar') }}" placeholder="karton / box / lusin">
                                @error('satuan_besar')
                                    <div class="invalid-feedback text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Isi Konversi <small class="text-muted">(Opsional)</small></label>
                                <input type="number" class="form-control" name="konversi_qty"
                                    value="{{ old('konversi_qty') }}" min="0.01" step="0.01" placeholder="12">
                                <span class="help-block">Contoh: 1 karton = 12 pcs</span>
                                @error('konversi_qty')
                                    <div class="invalid-feedback text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <div class="col-md-6 form-group"> --}}
                                {{-- <label for="">Harga Jual</label> --}}
                                {{-- <input type="text" class="form-control" name="harga_jual" --}}
                                    {{-- value="{{ old('harga_jual') }}" placeholder="Masukkan Harga Jual"> --}}
                                {{-- @error('harga_jual') --}}
                                    {{-- <div class="invalid-feedback text-danger"> --}}
                                        {{-- {{ $message }} --}}
                                    {{-- </div> --}}
                                {{-- @enderror --}}
                            {{-- </div> --}}
                            <div class="col-md-6 form-group">
                                <label>Category</label>
                                <select class="form-control select2" name="category_id" data-placeholder="Pilih Category"
                                    style="width: 100%;" required>
                                    <option value="" selected disabled>Pilih Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Minimum Stock -->
                            <div class="col-md-6 form-group">
                                <label for="">Minimum Stock</label>
                                <input type="number" class="form-control" name="min_stock"
                                    value="{{ old('min_stock', $product->min_stock ?? 0) }}" min="0"
                                    placeholder="0">
                                @error('min_stock')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Lokasi -->
                            <div class="col-md-6 form-group">
                                <label for="">Lokasi</label>
                                <input type="text" class="form-control" name="lokasi"
                                    value="{{ old('lokasi', $product->lokasi ?? '') }}"
                                    placeholder="Contoh: Rak A, Gudang 1">
                                @error('lokasi')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Multiple Select Supplier -->
                            <div class="col-md-6 form-group">
                                <label>Supplier</label>
                                <select class="form-control select2" name="supplier_ids[]" multiple
                                    data-placeholder="Pilih Supplier" style="width: 100%;">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ in_array($supplier->id, old('supplier_ids', $selectedSuppliers ?? [])) ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_ids')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                                @error('supplier_ids.*')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <a href="{{ route('product.index') }}" class="btn btn-default">Kembali</a>
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
            // Initialize select2 for multiple select
            $('.select2').select2({
                placeholder: "Pilih Supplier",
                allowClear: true
            });
        });
    </script>
@endsection
