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
                                                    style="width:100%" data-current-product="{{ $stock->product_id }}">
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control qty"
                                                    name="product[{{ $key }}][qty]" required
                                                    value="{{ $stock->product->is_serialized ? ($stock->serial_numbers ? count($stock->serial_numbers) : 1) : $stock->qty }}"
                                                    min="1" {{ $stock->product->is_serialized ? 'readonly' : '' }}>
                                            </td>
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

                            <div class="d-flex gap-2 mb-2">
                                <button class="btn btn-sm btn-warning" type="button" data-toggle="modal" data-target="#modalCekBarang">
                                    <i class="fa fa-search"></i> Cek Barang
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="addBahanBaku()" type="button">
                                    <i class="fa fa-plus"></i> Add Row
                                </button>
                            </div>
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

                        <!-- Modal Cek Barang -->
                        <div class="modal fade" id="modalCekBarang" tabindex="-1" role="dialog" aria-labelledby="modalCekBarangLabel">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="modalCekBarangLabel">
                                            <i class="fa fa-search"></i> Pilih Produk untuk PO
                                            <small class="text-warning">— diurutkan dari stok paling kritis</small>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <table id="tableCekBarang" class="table table-bordered table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th width="30"><input type="checkbox" id="checkAll"></th>
                                                    <th>Kode</th>
                                                    <th>Nama Produk</th>
                                                    <th>Stok Saat Ini</th>
                                                    <th>Min Stok</th>
                                                    <th>Status</th>
                                                    <th width="90">Qty Order</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cekBarangBody"></tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                        <button type="button" class="btn btn-primary" id="btnTambahkanPO">
                                            <i class="fa fa-check"></i> Tambahkan ke PO
                                        </button>
                                    </div>
                                </div>
                            </div>
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
        let productIndex = {{ count($pembelian->pembelianProducts) }};

        // Function to populate product selects with given products
        function populateProductSelects(products, target = '.product') {
            $(target).each(function() {
                let $select = $(this);
                // Prioritaskan data-current-product (dari Blade) lalu current value
                let currentProductId = $select.data('current-product') || $select.val();

                $select.empty().append('<option value="" disabled selected>Pilih Produk</option>');
                $.each(products, function(i, product) {
                    let stockText = product.stock_count ? ' [' + product.stock_count + ']' : '';
                    $select.append($('<option>', {
                        value: product.id,
                        text: product.code + ' ' + product.name + stockText,
                        'data-serialized': product.is_serialized ? 1 : 0
                    }));
                });

                // Set nilai yang sesuai
                if (currentProductId && products.some(p => p.id == currentProductId)) {
                    $select.val(currentProductId);
                }

                $select.trigger('change.select2');
            });

            updateSubtotalAndTotal();
        }

        // Muat semua produk saat halaman selesai dimuat (tanpa filter supplier)
        $(document).ready(function() {
            $.get('{{ route("pembelian.all-products") }}')
                .done(function(products) {
                    currentProducts = products;
                    populateProductSelects(products);
                    // Trigger change untuk mengambil harga beli yang tersimpan
                    $('.product').each(function() {
                        $(this).trigger('change');
                    });
                })
                .fail(function() {
                    alert('Gagal memuat daftar produk. Silakan refresh halaman.');
                });

            // Trigger input mask untuk perhitungan awal
            $('.harga_beli').each(function() {
                $(this).trigger('input');
            });
        });

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
                    <td><input type="text" required value="0" class="form-control harga_beli numeral-mask" name="product[${productIndex}][harga_beli]"></td>
                    <td><input type="text" required class="form-control subtotal" name="product[${productIndex}][subtotal]" readonly></td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeBahanBaku(this)" type="button">Remove</button></td>
                </tr>`;
            $('#product-repeater').append(productTemplate);

            let $newRow = $('#product-repeater tr:last');
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

        // Handle serial number input changes (jika kolom serial diaktifkan)
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
                let $hargaInput = $row.find('.harga_beli');
                let harga_beli = ($hargaInput.data('mask') !== undefined)
                    ? ($hargaInput.cleanVal() || 0)
                    : (parseFloat($hargaInput.val()) || 0);
                let subtotal = (qty || 0) * harga_beli;
                $row.find('.subtotal').val(formatRupiah(subtotal));
                total += subtotal;
            });
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
            let serialTextarea = $row.find('.serial-numbers');

            if (isProductSerialized) {
                if (serialContainer.length) serialContainer.show();
                if (noSerialMessage.length) noSerialMessage.hide();
                qtyInput.prop('readonly', true);
                if (!serialTextarea.val()) {
                    qtyInput.val(1);
                }
            } else {
                if (serialContainer.length) serialContainer.hide();
                if (noSerialMessage.length) noSerialMessage.show();
                qtyInput.prop('readonly', false);
                if (!qtyInput.val() || qtyInput.val() == 0) {
                    qtyInput.val(1);
                }
            }

            if (product_id) {
                $.get('/product/' + product_id, function(data) {
                    harga_beli.val(data.harga_beli).trigger('input');
                    updateSubtotalAndTotal();
                });
            }
        });

        // ---- Cek Barang Modal ----
        let cekBarangTable = null;

        $('#modalCekBarang').on('show.bs.modal', function (e) {
            if (!currentProducts || currentProducts.length === 0) {
                e.preventDefault();
                alert('Data produk belum dimuat. Coba muat ulang halaman.');
                return;
            }

            const sorted = [...currentProducts].sort((a, b) => {
                const aUnder = a.is_under_minimum ? 0 : 1;
                const bUnder = b.is_under_minimum ? 0 : 1;
                if (aUnder !== bUnder) return aUnder - bUnder;
                return a.stock_count - b.stock_count;
            });

            const tbody = $('#cekBarangBody');
            tbody.empty();

            sorted.forEach(function (p) {
                const isUnder = p.is_under_minimum;
                const suggestedQty = Math.max(1, (p.effective_min || p.min_stock || 0) - (p.stock_count || 0));

                const $tr = $('<tr>').addClass(isUnder ? 'danger' : '');

                const $checkTd = $('<td>').addClass('text-center').append(
                    $('<input>').attr({ type: 'checkbox', class: 'cek-product-check', value: p.id })
                        .data('name', p.name).data('harga', p.harga_beli || 0)
                );
                const $statusBadge = $('<span>').addClass('label')
                    .addClass(isUnder ? 'label-danger' : 'label-success')
                    .text(isUnder ? 'OUT OF STOCK' : 'Normal');
                const $qtyInput = $('<input>').attr({ type: 'number', class: 'form-control input-sm cek-qty', min: 1 })
                    .css('width', '70px').val(isUnder ? suggestedQty : 1);

                $tr.append(
                    $checkTd,
                    $('<td>').text(p.code),
                    $('<td>').text(p.name),
                    $('<td>').addClass('text-center').text(p.stock_count || 0),
                    $('<td>').addClass('text-center').text(p.effective_min || p.min_stock || 0),
                    $('<td>').addClass('text-center').append($statusBadge),
                    $('<td>').append($qtyInput)
                );

                tbody.append($tr);
            });

            if (cekBarangTable) {
                cekBarangTable.destroy();
            }
            cekBarangTable = $('#tableCekBarang').DataTable({
                retrieve: false,
                destroy: true,
                pageLength: 10,
                order: [],
                columnDefs: [
                    { orderable: false, targets: [0, 6] }
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_-_END_ dari _TOTAL_ produk",
                    paginate: { previous: "Prev", next: "Next" },
                    zeroRecords: "Tidak ada produk ditemukan"
                }
            });
        });

        $(document).on('change', '#checkAll', function () {
            const checked = $(this).prop('checked');
            if (cekBarangTable) {
                cekBarangTable.rows().nodes().each(function (node) {
                    $(node).find('.cek-product-check').prop('checked', checked);
                });
            } else {
                $('.cek-product-check').prop('checked', checked);
            }
        });

        $('#btnTambahkanPO').on('click', function () {
            const selected = [];
            if (cekBarangTable) {
                cekBarangTable.rows().nodes().each(function (node) {
                    const $check = $(node).find('.cek-product-check:checked');
                    if ($check.length) {
                        const $row = $(node);
                        selected.push({
                            product_id: $check.val(),
                            name: $check.data('name'),
                            harga: $check.data('harga'),
                            qty: parseInt($row.find('.cek-qty').val()) || 1
                        });
                    }
                });
            } else {
                $('#cekBarangBody .cek-product-check:checked').each(function () {
                    const $row = $(this).closest('tr');
                    selected.push({
                        product_id: $(this).val(),
                        name: $(this).data('name'),
                        harga: $(this).data('harga'),
                        qty: parseInt($row.find('.cek-qty').val()) || 1
                    });
                });
            }

            if (selected.length === 0) {
                alert('Pilih minimal satu produk.');
                return;
            }

            // Hapus baris pertama jika masih kosong (belum dipilih produk)
            const $firstRow = $('#product-repeater tr:first');
            if ($firstRow.find('.product').val() === null || $firstRow.find('.product').val() === '') {
                $firstRow.remove();
            }

            selected.forEach(function (item) {
                addBahanBaku();

                const $newRow = $('#product-repeater tr:last');
                const $productSelect = $newRow.find('.product');
                const $hargaInput = $newRow.find('.harga_beli');
                const $qtyInput = $newRow.find('.qty');

                // Set pilihan produk tanpa trigger change (untuk hindari AJAX sebelum opsi siap)
                $productSelect.val(item.product_id).trigger('change.select2');

                // Isi harga dari data cache
                $hargaInput.val(item.harga).trigger('input');

                // Isi qty
                $qtyInput.val(item.qty);

                updateSubtotalAndTotal();
            });

            $('#modalCekBarang').modal('hide');
        });
    </script>
@endsection