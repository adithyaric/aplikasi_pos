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
                            <div class="form-group">
                                <div id="product-repeater">
                                    <div class="row">
                                        <div class="form-group col-sm-2">
                                            <label>Product</label>
                                            <select required class="form-control select2" name="product[0][product_id]"
                                                data-placeholder="Pilih Product" style="width: 100%;">
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label>Qty</label>
                                            <input type="text" required class="form-control qty" name="product[0][qty]">
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label>Expired</label>
                                            <input type="date" required class="form-control" name="product[0][expired]">
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label>Harga Beli</label>
                                            <input type="text" required class="form-control harga_beli numeral-mask"
                                                name="product[0][harga_beli]">
                                        </div>
                                        <!-- Add a new div for the subtotal -->
                                        <div class="form-group col-sm-2">
                                            <label>Subtotal</label>
                                            <input type="text" required class="form-control subtotal"
                                                name="product[0][subtotal]" readonly>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="addBahanBaku()">Add</button>
                            </div>
                            <!-- Add a new div for the total outside the repeater -->
                            <hr>
                            <div class="form-group">
                                <label>Total</label>
                                <input type="text" required class="form-control" name="total" id="total" readonly>
                            </div>
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
            <div class="form-group col-sm-2">
                <label>Product</label>
                <select required class="form-control select2" name="product[${productIndex}][product_id]"
                    data-placeholder="Pilih Product" style="width: 100%;">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label>Qty</label>
                <input type="text" required class="form-control qty" name="product[${productIndex}][qty]">
            </div>
            <div class="form-group col-sm-2">
                <label>Expired</label>
                <input type="date" required class="form-control" name="product[${productIndex}][expired]">
            </div>
            <div class="form-group col-sm-2">
                <label>Harga Beli</label>
                <input type="text" required class="form-control harga_beli numeral-mask"
                    name="product[${productIndex}][harga_beli]">
            </div>
            <!-- Add a new div for the subtotal -->
            <div class="form-group col-sm-2">
                <label>Subtotal</label>
                <input type= "text" required class= "form-control subtotal"
                    name="product[${productIndex}][subtotal]" readonly >
            </div >
        </div >
    `;
            $('#product-repeater').append(productTemplate);
            $('#product-repeater .select2').last().select2();
            updateSubtotalAndTotal();
        }

        // Add event listeners to update the subtotal and total when the quantity or price changes
        $(document).on('change', '.qty, .harga_beli', function() {
            updateSubtotalAndTotal();
        });

        // Function to update the subtotal and total
        function updateSubtotalAndTotal() {
            let total = 0;
            $('.row').each(function() {
                let qty = $(this).find('.qty').val();
                let harga_beli = $(this).find('.harga_beli').cleanVal();
                let subtotal = qty * harga_beli;
                $(this).find('.subtotal').val(subtotal);
            });
            $('.subtotal').each(function() {
                let subtotal = parseInt($(this).val());
                total += subtotal;
            });
            $('#total').val(total);
        }
        $('.numeral-mask').mask("#,##0", {
            reverse: true
        });
        updateSubtotalAndTotal();
    </script>
@endsection
