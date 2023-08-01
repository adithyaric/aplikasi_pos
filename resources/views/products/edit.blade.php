@extends('layouts.master')

@section('title', 'Edit Product')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Product</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label for="">Gambar</label>
                                <img class="img-thumbnail" src="{{ asset($product->pic) }}" alt="" width="200px">
                                <input type="file" class="form-control" name="pic"
                                    value="{{ old('pic', $product->pic) }}" placeholder="Masukkan Gambar">
                                @error('pic')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Nama</label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ old('name', $product->name) }}" placeholder="Masukkan Nama">
                                @error('name')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Code</label>
                                <input type="text" class="form-control" name="code"
                                    value="{{ old('code', $product->code) }}" placeholder="Masukkan Code">
                                @error('code')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Deskripsi</label>
                                <input type="text" class="form-control" name="desc"
                                    value="{{ old('desc', $product->desc) }}" placeholder="Masukkan Deskripsi">
                                @error('desc')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Warna</label>
                                <input type="text" class="form-control" name="warna"
                                    value="{{ old('warna', $product->warna) }}" placeholder="Masukkan Warna">
                                @error('warna')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Ukuran</label>
                                <input type="text" class="form-control" name="ukuran"
                                    value="{{ old('ukuran', $product->ukuran) }}" placeholder="Masukkan Ukuran">
                                @error('ukuran')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Berat</label>
                                <input type="text" class="form-control" name="berat"
                                    value="{{ old('berat', $product->berat) }}" placeholder="Masukkan Berat">
                                @error('berat')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Harga Beli</label>
                                <input type="text" class="form-control" name="harga_beli"
                                    value="{{ old('harga_beli', $product->harga_beli) }}"
                                    placeholder="Masukkan Harga Beli">
                                @error('harga_beli')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Diskon</label>
                                <input type="text" class="form-control" name="diskon"
                                    value="{{ old('diskon', $product->diskon) }}"
                                    placeholder="Masukkan Diskon">
                                @error('diskon')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="">Harga Jual</label>
                                <input type="text" class="form-control" name="harga_jual"
                                    value="{{ old('harga_jual', $product->harga_jual) }}"
                                    placeholder="Masukkan Harga Jual">
                                @error('harga_jual')
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
                                            {{ old('outlet_id', $product->outlet_id) == $outlet->id ? 'selected' : '' }}>
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
                                            {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control select2" name="category_id" data-placeholder="Pilih Category"
                                    style="width: 100%;">
                                    <option value="" selected disabled>Pilih Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
