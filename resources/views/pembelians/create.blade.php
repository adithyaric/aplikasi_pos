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
                                <input type="text" class="form-control" name="code" value="{{ old('code', $code) }}"
                                    placeholder="Masukkan Kode Pembelian">
                                @error('code')
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
                            <table class="table table-bordered table-striped" id="example">
                                <thead>
                                    <tr>
                                        <td>Nama Product</td>
                                        <td>Qty</td>
                                        <td>Harga Beli</td>
                                        <td>Sub Total</td>
                                        <td>Aksi</td>
                                    </tr>
                                </thead>
                                <tbody id="product-repeater">
                                    <tr>
                                        <td>
                                            <select class="form-control select2 product" data-placeholder="Pilih Product"
                                                name="product[0][product_id]" required style="width:100%">
                                                <option value="" disabled selected>Pilih Produk</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control qty" name="product[0][qty]" required
                                                value="1" min="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control harga_beli numeral-mask"
                                                name="product[0][harga_beli]" required>
                                        </td>
                                        <td>
                                            <input class="form-control subtotal" name="product[0][subtotal]" required
                                                readonly>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="removeBahanBaku(this)"
                                                type="button">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- //TODO add Product pop-up (nanti bisa lanjut di select²) --}}
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
        let currentProducts = null;
        let productIndex = 0;

        // Load all products on page load (no supplier filter)
        $.get('{{ route("pembelian.all-products") }}', function(products) {
            currentProducts = products;
            populateProductSelects(products);
        });

        function populateProductSelects(products, target = '.product') {
            $(target).each(function() {
                let $select = $(this);
                let currentVal = $select.val(); // preserve selected value if still valid

                $select.empty().append('<option value="" disabled selected>Pilih Produk</option>');
                $.each(products, function(i, product) {
                    // Include stock count if your API returns it; otherwise omit.
                    let stockText = product.stock_count ? ' [' + product.stock_count + ']' : '';
                    //TODO add rekomendasi kekurangan stock ($product->min_stock - $product->stocks()->sum('qty_available'))
                    $select.append($('<option>', {
                        value: product.id,
                        text: product.code + ' ' + product.name + stockText,
                        'data-serialized': product.is_serialized ? 1 : 0
                    }));
                });

                // Try to reselect previous value if it exists in new options
                if (currentVal && products.some(p => p.id == currentVal)) {
                    $select.val(currentVal);
                }

                // Refresh Select2
                $select.trigger('change.select2');
            });
        }

        // Helper: format number with thousand separators (Indonesian style)
        function formatRupiah(angka) {
            if (!angka) return '0';
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function addBahanBaku() {
            productIndex++;
            let productTemplate = `
                <tr>
                    <td>
                        <select required class="form-control select2 product" name="product[${productIndex}][product_id]" data-placeholder="Pilih Product" style="width:100%;">
                            <option value="" disabled selected>Pilih Produk</option>
                        </select>
                    </td>
                    <td><input type="number" required value="1" min="1" class="form-control qty" name="product[${productIndex}][qty]"></td>
                    <td><input required type="text" class="form-control harga_beli numeral-mask" name="product[${productIndex}][harga_beli]"></td>
                    <td><input type="text" required class="form-control subtotal" name="product[${productIndex}][subtotal]" readonly></td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeBahanBaku(this)" type="button">Remove</button></td>
                </tr>`;
            $('#product-repeater').append(productTemplate);

            let $newRow = $('#product-repeater tr:last');
            // Reinitialize mask on new harga_beli
            $newRow.find('.numeral-mask').mask("#,##0", { reverse: true });
            $newRow.find('.select2').select2();

            if (currentProducts) {
                populateProductSelects(currentProducts, $newRow.find('.product'));
            }

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
                let $row = $(this);
                let qty = $row.find('.qty').val();
                let harga_beli = $row.find('.harga_beli').cleanVal(); // numeric value from masked input
                let subtotal = qty * harga_beli;
                // Set formatted subtotal (readonly)
                $row.find('.subtotal').val(formatRupiah(subtotal));
                total += subtotal;
            });
            // Set formatted total
            $('#total').val(formatRupiah(total));
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
            let $row = $(this).closest('tr');
            let harga_beli = $row.find('.harga_beli');
            let product_id = $(this).val();
            let isProductSerialized = $(this).find('option:selected').data('serialized');
            let serialContainer = $row.find('.serial-container');
            let noSerialMessage = $row.find('.no-serial-message');
            let qtyInput = $row.find('.qty');

            if (isProductSerialized) {
                serialContainer.show();
                noSerialMessage.hide();
                qtyInput.prop('readonly', true);
                qtyInput.val(1);
            } else {
                serialContainer.hide();
                noSerialMessage.show();
                qtyInput.prop('readonly', false);
                qtyInput.val(1);
            }

            $.get('/product/' + product_id, function(data) {
                // Set raw value and trigger input to apply mask formatting
                harga_beli.val(data.harga_beli).trigger('input');
                updateSubtotalAndTotal();
            });
        });

        // Handle product change on page load for existing rows
        $(document).ready(function() {
            $('.product').each(function() {
                $(this).trigger('change');
            });
            $('.harga_beli').each(function() {
                $(this).trigger('input');
            });
        });

        $('#kas').prop('disabled', true);
        $('#outlet').on('change', function() {
            let outlet_id = $(this).val();
            $.get('/outlet/' + outlet_id + '/kas', function(data) {
                $('#kas').find('option').remove();
                let defaultOption = $('<option>').val('').text('Pilih Kas').prop('disabled', true).prop(
                    'selected', true);
                $('#kas').append(defaultOption);
                data.forEach(function(kas) {
                    let option = $('<option>').val(kas.id).text(kas.name);
                    $('#kas').append(option);
                });
                $('#kas').trigger('change.select2');
            });
            $('#kas').prop('disabled', false);
        });
    </script>
@endsection
