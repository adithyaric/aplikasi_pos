@extends('layouts.master')

@section('title', 'Tambah Retur Pembelian')

@section('container')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tambah Retur Pembelian</h3>
                    </div>

                    <form action="{{ route('refundPembelian.store') }}" method="POST">
                        @csrf

                        {{-- Hidden type field, updated when tab changes --}}
                        <input type="hidden" name="type" id="type" value="gudang_ke_supplier">

                        <div class="box-body">

                            {{-- ── Type Tab Selector ── --}}
                            <ul class="nav nav-tabs" id="typeTab" style="margin-bottom:20px">
                                <li class="active">
                                    <a href="#tab-supplier" data-toggle="tab" data-type="gudang_ke_supplier">
                                        <i class="fa fa-arrow-up"></i> Gudang ke Supplier
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-outlet" data-toggle="tab" data-type="outlet_ke_gudang">
                                        <i class="fa fa-arrow-down"></i> Outlet ke Gudang
                                    </a>
                                </li>
                            </ul>

                            {{-- ── Common Fields ── --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Retur</label>
                                        <input type="text" class="form-control" name="code"
                                            value="{{ old('code', $code) }}" required>
                                        @error('code')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="date" class="form-control" name="tanggal"
                                            value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                        @error('tanggal')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content">

                                {{-- ══════════════════════════════════════════════
                            TAB 1: Gudang ke Supplier
                            ══════════════════════════════════════════════ --}}
                                <div class="tab-pane active" id="tab-supplier">
                                    <div class="form-group">
                                        <label>Supplier <span class="text-danger">*</span></label>
                                        <select id="supplier_id" class="form-control select2" name="supplier_id"
                                            data-placeholder="Pilih Supplier" style="width:100%">
                                            <option value="" disabled selected>Pilih Supplier</option>
                                            @foreach ($suppliers as $s)
                                                <option value="{{ $s->id }}"
                                                    {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="supplier-product-area" style="display:none">
                                        <h4>Daftar Produk Gudang</h4>
                                        <div class="text-muted small" style="margin-bottom:8px">
                                            Produk otomatis dari stok gudang supplier. Hapus baris yang tidak diretur.
                                        </div>
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-bordered table-striped" id="tbl-supplier">
                                                <thead class="bg-light-blue">
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th width="80">SKU/Batch</th>
                                                        <th width="70">No. PO</th>
                                                        <th width="70">Tersedia</th>
                                                        <th width="80">Qty Retur</th>
                                                        <th width="110">Harga Satuan</th>
                                                        <th>Alasan</th>
                                                        <th width="60">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="supplier-repeater"></tbody>
                                            </table>
                                        </div>
                                        <div class="form-group">
                                            <label>Total Retur (IDR)</label>
                                            <input type="text" class="form-control numeral-mask" name="total"
                                                id="total-supplier" readonly value="0">
                                        </div>
                                    </div>
                                    <div id="supplier-loading" style="display:none" class="text-center text-muted">
                                        <i class="fa fa-spinner fa-spin"></i> Memuat produk...
                                    </div>
                                    <div id="supplier-empty" style="display:none" class="alert alert-warning">
                                        Tidak ada stok gudang untuk supplier ini.
                                    </div>
                                </div>

                                {{-- ══════════════════════════════════════════════
                            TAB 2: Outlet ke Gudang
                            ══════════════════════════════════════════════ --}}
                                <div class="tab-pane" id="tab-outlet">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Outlet <span class="text-danger">*</span></label>
                                                <select id="outlet_id" class="form-control select2" name="outlet_id"
                                                    data-placeholder="Pilih Outlet" style="width:100%" disabled>
                                                    <option value="" disabled selected>Pilih Outlet</option>
                                                    @foreach ($outlets as $o)
                                                        <option value="{{ $o->id }}"
                                                            {{ old('outlet_id') == $o->id ? 'selected' : '' }}>
                                                            {{ $o->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('outlet_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{-- [CHANGED] Select Delivery Order, not Pembelian --}}
                                                <label>No. Delivery Order <span class="text-danger">*</span></label>
                                                <select id="delivery_order_id" class="form-control select2"
                                                    name="delivery_order_id" data-placeholder="Pilih Delivery Order"
                                                    style="width:100%" disabled>
                                                    <option value="" disabled selected>Pilih Delivery Order</option>
                                                </select>
                                                @error('delivery_order_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div id="outlet-product-area" style="display:none">
                                        <h4>Daftar Produk Outlet</h4>
                                        <div class="text-muted small" style="margin-bottom:8px">
                                            Produk dari delivery order tersebut. Hapus baris yang tidak diretur.
                                        </div>
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-bordered table-striped" id="tbl-outlet">
                                                <thead class="bg-green">
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th width="80">SKU</th>
                                                        <th width="70">Tersedia</th>
                                                        <th width="80">Qty Retur</th>
                                                        <th>Alasan</th>
                                                        <th width="60">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="outlet-repeater"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="outlet-loading" style="display:none" class="text-center text-muted">
                                        <i class="fa fa-spinner fa-spin"></i> Memuat produk...
                                    </div>
                                    <div id="outlet-empty" style="display:none" class="alert alert-warning">
                                        Tidak ada stok outlet untuk delivery order ini.
                                    </div>
                                </div>

                            </div>{{-- end tab-content --}}

                        </div>{{-- end box-body --}}

                        <div class="box-footer">
                            <a href="{{ route('refundPembelian.index') }}" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
                                <i class="fa fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        // ── Tab switching ──────────────────────────────────────────────────────────
        $('#typeTab a[data-toggle="tab"]').on('shown.bs.tab', function() {
            var type = $(this).data('type');
            $('#type').val(type);

            if (type === 'gudang_ke_supplier') {
                $('#outlet_id, #delivery_order_id').prop('disabled', true).removeAttr('required');
                $('#supplier_id').prop('disabled', false);
            } else {
                $('#supplier_id').prop('disabled', true).removeAttr('required');
                $('#outlet_id').prop('disabled', false).attr('required', true);
            }

            checkSubmit();
        });

        // ── Numeral mask ──────────────────────────────────────────────────────────
        function applyMask() {
            $('.numeral-mask').mask("#,##0", {
                reverse: true
            });
        }
        applyMask();

        // ── Submit guard ──────────────────────────────────────────────────────────
        function checkSubmit() {
            var type = $('#type').val();
            var hasRows = false;
            if (type === 'gudang_ke_supplier') {
                hasRows = $('#supplier-repeater tr').length > 0;
            } else {
                hasRows = $('#outlet-repeater tr').length > 0;
            }
            $('#btn-submit').prop('disabled', !hasRows);
        }

        // ── Remove row ────────────────────────────────────────────────────────────
        function removeRow(btn) {
            $(btn).closest('tr').remove();
            recalcTotal();
            checkSubmit();
        }

        // ── Recalc total (supplier only) ──────────────────────────────────────────
        function recalcTotal() {
            var total = 0;
            $('#supplier-repeater tr').each(function() {
                var qty = parseInt($(this).find('.input-qty').val()) || 0;
                var harga = parseInt($(this).find('.input-harga').val().replace(/,/g, '')) || 0;
                total += qty * harga;
            });
            // reformat
            $('#total-supplier').val(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        }

        // ── TAB 1: Supplier change → load warehouse stocks ────────────────────────
        $('#supplier_id').on('change', function() {
            var supplierId = $(this).val();
            if (!supplierId) return;

            $('#supplier-product-area, #supplier-empty').hide();
            $('#supplier-loading').show();
            $('#supplier-repeater').empty();
            $('#btn-submit').prop('disabled', true);

            $.get('/retur/supplier/' + supplierId + '/products', function(data) {
                $('#supplier-loading').hide();

                if (!data.length) {
                    $('#supplier-empty').show();
                    return;
                }

                $.each(data, function(i, item) {
                    var row = `
                    <tr>
                        <td>
                            ${item.product_name}
                            <input type="hidden" name="product[${i}][product_id]" value="${item.product_id}">
                            <input type="hidden" name="product[${i}][stock_id]" value="${item.stock_id}">
                            <input type="hidden" name="product[${i}][sku]" value="${item.sku}">
                        </td>
                        <td><span class="label label-default">${item.sku}</span></td>
                        <td><small class="text-muted">${item.pembelian_code}</small></td>
                        <td><span class="badge bg-blue">${item.qty_available}</span></td>
                        <td>
                            <input type="number" class="form-control input-qty" style="width:70px" name="product[${i}][qty]" value="1"
                                min="1" max="${item.qty_available}" required>
                        </td>
                        <td>
                            <input type="text" class="form-control numeral-mask input-harga" style="width:100px" name="product[${i}][harga]"
                                value="${item.harga_beli}" required>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="product[${i}][alasan]" placeholder="Alasan retur..." required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                    $('#supplier-repeater').append(row);
                });

                applyMask();
                $('#supplier-product-area').show();
                recalcTotal();
                checkSubmit();

                // Recalc on qty/harga change
                $('#supplier-repeater').off('input', '.input-qty, .input-harga')
                    .on('input', '.input-qty, .input-harga', function() {
                        recalcTotal();
                    });
            }).fail(function() {
                $('#supplier-loading').hide();
                alert('Gagal memuat produk supplier.');
            });
        });

        // ── TAB 2: Outlet change → load delivery orders ───────────────────────────
        $('#outlet_id').on('change', function() {
            var outletId = $(this).val();
            if (!outletId) return;

            $('#delivery_order_id').prop('disabled', true).find('option:not(:first)').remove();
            $('#outlet-product-area, #outlet-empty').hide();
            $('#outlet-repeater').empty();
            $('#btn-submit').prop('disabled', true);

            $.get('/retur/outlet/' + outletId + '/delivery-orders', function(data) {
                if (!data.length) {
                    alert('Tidak ada delivery order yang sudah diterima untuk outlet ini.');
                    return;
                }
                $.each(data, function(i, do_) {
                    var label = do_.code + (do_.received_date ? ' (' + do_.received_date + ')' :
                    '');
                    $('#delivery_order_id').append($('<option>').val(do_.id).text(label));
                });
                $('#delivery_order_id').prop('disabled', false).trigger('change.select2');
            });
        });

        // ── TAB 2: Delivery Order change → load outlet stocks ────────────────────
        $('#delivery_order_id').on('change', function() {
            var doId = $(this).val();
            if (!doId) return;

            $('#outlet-product-area, #outlet-empty').hide();
            $('#outlet-loading').show();
            $('#outlet-repeater').empty();
            $('#btn-submit').prop('disabled', true);

            $.get('/retur/delivery-order/' + doId + '/items', function(data) {
                $('#outlet-loading').hide();

                if (!data.length) {
                    $('#outlet-empty').show();
                    return;
                }

                $.each(data, function(i, item) {
                    var row = `
            <tr>
                <td>
                    ${item.product_name}
                    <input type="hidden" name="product[${i}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="product[${i}][stock_id]" value="${item.stock_id}">
                </td>
                <td><span class="label label-default">${item.sku}</span></td>
                <td><span class="badge bg-green">${item.qty_available}</span></td>
                <td>
                    <input type="number" class="form-control" style="width:70px"
                        name="product[${i}][qty]" value="1"
                        min="1" max="${item.qty_available}" required>
                </td>
                <td>
                    <input type="text" class="form-control" name="product[${i}][alasan]"
                        placeholder="Alasan retur..." required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
                    $('#outlet-repeater').append(row);
                });

                $('#outlet-product-area').show();
                checkSubmit();
            }).fail(function() {
                $('#outlet-loading').hide();
                alert('Gagal memuat stok outlet.');
            });
        });
    </script>
@endsection
