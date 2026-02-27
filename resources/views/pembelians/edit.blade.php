@extends('layouts.master')

@section('title', 'Edit Pembelian')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Pembelian</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label for="">Kode Pembelian</label>
                                <input type="text" class="form-control" name="code"
                                    value="{{ old('code', $pembelian->code) }}" placeholder="Masukkan Kode Pembelian">
                                @error('code')
                                    <div class="invalid-feedback text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- <div class="form-group"> --}}
                                {{-- <label>Outlet</label> --}}
                                {{-- <select class="form-control select2" name="outlet_id" data-placeholder="Pilih Outlet" --}}
                                    {{-- style="width: 100%;"> --}}
                                    {{-- <option value="" selected disabled>Pilih Outlet</option> --}}
                                    {{-- @foreach ($outlets as $outlet) --}}
                                        {{-- <option value="{{ $outlet->id }}" --}}
                                            {{-- {{ old('outlet_id', $pembelian->outlet_id) == $outlet->id ? 'selected' : '' }}> --}}
                                            {{-- {{ $outlet->name }} --}}
                                        {{-- </option> --}}
                                    {{-- @endforeach --}}
                                {{-- </select> --}}
                                {{-- @error('outlet_id') --}}
                                    {{-- <div class="invalid-feedback text-danger"> --}}
                                        {{-- {{ $message }} --}}
                                    {{-- </div> --}}
                                {{-- @enderror --}}
                            {{-- </div> --}}
                            <div class="form-group">
                                <label>Supplier</label>
                                <select class="form-control select2" name="supplier_id" data-placeholder="Pilih Supplier"
                                    style="width: 100%;">
                                    <option value="" selected disabled>Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id', $pembelian->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                            {{-- <div class="form-group"> --}}
                                {{-- <label>Kas</label> --}}
                                {{-- <select class="form-control select2" name="kas_id" data-placeholder="Pilih Kas" --}}
                                    {{-- style="width: 100%;"> --}}
                                    {{-- <option value="" selected disabled>Pilih Supplier</option> --}}
                                    {{-- @foreach ($kas as $kas) --}}
                                        {{-- <option value="{{ $kas->id }}" --}}
                                            {{-- {{ old('kas_id', $pembelian->kas_id) == $kas->id ? 'selected' : '' }}> --}}
                                            {{-- {{ $kas->name }} --}}
                                        {{-- </option> --}}
                                    {{-- @endforeach --}}
                                {{-- </select> --}}
                                {{-- @error('kas_id') --}}
                                    {{-- <div class="invalid-feedback text-danger"> --}}
                                        {{-- {{ $message }} --}}
                                    {{-- </div> --}}
                                {{-- @enderror --}}
                            {{-- </div> --}}
                            <hr>
                            <table class="table table-bordered table-striped" id="example">
                                <thead>
                                    <tr>
                                        <td>Nama Product</td>
                                        <td>Qty</td>
                                        {{-- <td>Serial Numbers</td> --}}
                                        <td>Harga Beli</td>
                                        <td>Sub Total</td>
                                        <td>Aksi</td>
                                    </tr>
                                </thead>
                                <tbody id="product-repeater">
                                    @foreach ($pembelian->pembelianProducts as $key => $stock)
                                        <tr>
                                            <td>
                                                <select class="form-control select2 product"
                                                    data-placeholder="Pilih Product"
                                                    name="product[{{ $key }}][product_id]" required
                                                    style="width:100%">
                                                    @foreach ($products as $product)
                                                        <option {{ $stock->product_id == $product->id ? 'selected' : '' }}
                                                            value="{{ $product->id }}"
                                                            data-serialized="{{ $product->is_serialized ? 1 : 0 }}">
                                                            {{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control qty"
                                                    name="product[{{ $key }}][qty]" required
                                                    value="{{ $stock->product->is_serialized ? ($stock->serial_numbers ? count($stock->serial_numbers) : 1) : $stock->qty }}"
                                                    min="1" {{ $stock->product->is_serialized ? 'readonly' : '' }}>
                                            </td>
                                            {{-- <td> --}}
                                                {{-- <div class="serial-container" --}}
                                                    {{-- style="display: {{ $stock->product->is_serialized ? 'block' : 'none' }};"> --}}
                                                    {{-- <textarea class="form-control serial-numbers" name="product[{{ $key }}][serial_numbers]" --}}
                                                        {{-- placeholder="Enter serial numbers (one per line)" rows="3">{{ $stock->serial_numbers ? implode("\n", $stock->serial_numbers) : '' }}</textarea> --}}
                                                    {{-- <small class="text-muted">Enter one serial number per line</small> --}}
                                                {{-- </div> --}}
                                                {{-- <div class="no-serial-message" --}}
                                                    {{-- style="display: {{ $stock->product->is_serialized ? 'none' : 'block' }};"> --}}
                                                    {{-- <small class="text-muted">No serial numbers needed</small> --}}
                                                {{-- </div> --}}
                                            {{-- </td> --}}
                                            <td>
                                                <input type="text" class="form-control harga_beli numeral-mask"
                                                    name="product[{{ $key }}][harga_beli]" required
                                                    value="{{ $stock->harga_beli }}">
                                            </td>
                                            <td>
                                                <input class="form-control subtotal"
                                                    name="product[{{ $key }}][subtotal]" required readonly>
                                            </td>
                                            <td>
                                                <a class="btn btn-danger btn-group-sm"
                                                    href="{{ route('pembelian.stock.destroy', $stock->id) }}">
                                                    <li class="fa fa-trash"></li>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <button class="btn btn-sm btn-primary" onclick="addBahanBaku()" type="button">Add</button>
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
        let productIndex = {{ count($pembelian->pembelianProducts) }};

        function addBahanBaku() {
            productIndex++;
            let productTemplate = `
        <tr>
            <td>
                <select required class="form-control select2 product" name="product[${productIndex}][product_id]" data-placeholder="Pilih Product" style="width:100%;">
                    <option value="" disabled selected>Pilih Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-serialized="{{ $product->is_serialized ? 1 : 0 }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" required value="1" min="1" class="form-control qty" name="product[${productIndex}][qty]"></td>
            <td>
                <div class="serial-container" style="display: none;">
                    <textarea class="form-control serial-numbers" name="product[${productIndex}][serial_numbers]" placeholder="Enter serial numbers (one per line)" rows="3"></textarea>
                    <small class="text-muted">Enter one serial number per line</small>
                </div>
                <div class="no-serial-message" style="display: block;">
                    <small class="text-muted">No serial numbers needed</small>
                </div>
            </td>
            <td><input type="text" required value="0" class="form-control harga_beli numeral-mask" name="product[${productIndex}][harga_beli]"></td>
            <td><input type="text" required class="form-control subtotal" name="product[${productIndex}][subtotal]" readonly></td>
            <td><button class="btn btn-sm btn-danger" onclick="removeBahanBaku(this)" type="button">Remove</button></td>
        </tr>`;
            $('#product-repeater').append(productTemplate);
            $('#product-repeater .select2').last().select2();
            updateSubtotalAndTotal();
        }

        $(document).on('change', '.qty, .harga_beli', function() {
            updateSubtotalAndTotal();
        });

        // Handle serial number input changes
        $(document).on('input', '.serial-numbers', function() {
            let serialText = $(this).val();
            let serialLines = serialText.split('\n').filter(line => line.trim() !== '');
            let qtyInput = $(this).closest('tr').find('.qty');
            let isProductSerialized = $(this).closest('tr').find('.product option:selected').data('serialized');

            if (isProductSerialized) {
                qtyInput.val(serialLines.length);
                updateSubtotalAndTotal();
            }
        });

        function updateSubtotalAndTotal() {
            let total = 0;
            $('tbody tr').each(function() {
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

        function removeBahanBaku(button) {
            if ($('#example tbody tr').length > 1) {
                $(button).closest('tr').remove();
                updateSubtotalAndTotal();
            }
        }

        $(document).on('change', '.product', function() {
            let harga_beli = $(this).closest('tr').find('.harga_beli');
            let product_id = $(this).val();
            let isProductSerialized = $(this).find('option:selected').data('serialized');
            let row = $(this).closest('tr');
            let serialContainer = row.find('.serial-container');
            let noSerialMessage = row.find('.no-serial-message');
            let qtyInput = row.find('.qty');
            let serialTextarea = row.find('.serial-numbers');

            // Show/hide serial number input based on product type
            if (isProductSerialized) {
                serialContainer.show();
                noSerialMessage.hide();
                qtyInput.prop('readonly', true);

                // Only set qty to 1 if this is a new row (no existing serial numbers)
                if (!serialTextarea.val()) {
                    qtyInput.val(1);
                }
            } else {
                serialContainer.hide();
                noSerialMessage.show();
                qtyInput.prop('readonly', false);

                // Only reset qty if this is a new row
                if (!qtyInput.val() || qtyInput.val() == 0) {
                    qtyInput.val(1);
                }
            }

            $.get('/product/' + product_id, function(data) {
                harga_beli.val(data.harga_beli);
                updateSubtotalAndTotal();
            });
        });

        // Handle product change on page load for existing rows
        $(document).ready(function() {
            $('.product').each(function() {
                $(this).trigger('change');
            });
        });
    </script>
@endsection
