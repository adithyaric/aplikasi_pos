@extends('layouts.master')
@section('title', 'Stock Opname')
@section('container')
    <section class="content-header">
        <h1>Stock Opname</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><strong>STOCK OPNAME</strong></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-borderless mb-2" style="width: 40%">
                            <tr>
                                <td>Tanggal Stock Opname</td>
                                <td>:
                                    <input type="date" id="tglStockOpname" class="form-control"
                                        value="{{ date('Y-m-d') }}" />
                                </td>
                            </tr>
                        </table>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Satuan</th>
                                        <th>Stock Fisik</th>
                                        <th>Stock di Kartu</th>
                                        <th>Selisih</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex justify-content-between">
                            <button id="tambahBaris" class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i> Tambah Baris
                            </button>
                            <button class="btn btn-success" id="btnSaveOpname">
                                <i class="fa fa-save"></i> Save Stock Opname
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-script')
    <script>
        let allStockData = [];

        $(document).ready(function() {
            loadStockData();

            function loadStockData() {
                $.get('{{ route('stock.opname.data') }}', function(data) {
                    allStockData = data.stocks;
                    renderInitialRows();
                }).fail(function() {
                    alert('Gagal memuat data stock');
                });
            }

            function renderInitialRows() {
                const tbody = $('#tableBody');
                tbody.empty();

                allStockData.forEach((item, index) => {
                    const newRow = `
                <tr>
                    <td>${index + 1}</td>
                    <td><input type="text" class="form-control product-name" value="${item.product_name}"
                               data-stock-id="${item.id}" data-product-id="${item.product_id}" disabled /></td>
                    <td><input type="text" class="form-control sku" value="${item.sku}" disabled /></td>
                    <td><input type="text" class="form-control satuan" value="${item.satuan}" disabled /></td>
                    <td><input type="number" step="0.01" class="form-control stock_fisik" value="${item.qty}" /></td>
                    <td><input type="number" class="form-control stock_dikartu" value="${item.qty}" disabled /></td>
                    <td><input type="number" step="0.01" class="form-control selisih" value="0" disabled /></td>
                    <td><input type="text" class="form-control keterangan" value="${item.keterangan}" /></td>
                </tr>
            `;
                    tbody.append(newRow);
                });

                attachEventListeners();
            }

            function attachEventListeners() {
                $('.stock_fisik').off('input').on('input', function() {
                    const row = $(this).closest('tr');
                    const stockFisik = parseFloat($(this).val()) || 0;
                    const stockKartu = parseFloat(row.find('.stock_dikartu').val()) || 0;
                    const selisih = (stockFisik - stockKartu).toFixed(2);
                    row.find('.selisih').val(selisih);
                });
            }

            function updateNomorUrut() {
                $('#tableBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            $('#tambahBaris').on('click', function(e) {
                e.preventDefault();
                const newRow = `
            <tr>
                <td></td>
                <td>
                    <select class="form-control select-stock">
                        <option value="">-- Pilih Stock --</option>
                        ${allStockData.map(s => `
                                    <option value="${s.id}"
                                            data-product-id="${s.product_id}"
                                            data-sku="${s.sku}"
                                            data-satuan="${s.satuan}"
                                            data-qty="${s.qty}">
                                        ${s.product_name} - SKU: ${s.sku}
                                    </option>
                                `).join('')}
                    </select>
                </td>
                <td><input type="text" class="form-control sku" disabled /></td>
                <td><input type="text" class="form-control satuan" disabled /></td>
                <td><input type="number" step="0.01" class="form-control stock_fisik" value="0" /></td>
                <td><input type="number" class="form-control stock_dikartu" disabled /></td>
                <td><input type="number" step="0.01" class="form-control selisih" disabled /></td>
                <td><input type="text" class="form-control keterangan" /></td>
            </tr>
        `;
                $('#tableBody').append(newRow);
                updateNomorUrut();

                // Handle new select
                const lastRow = $('#tableBody tr:last');
                lastRow.find('.select-stock').on('change', function() {
                    const selected = $(this).find(':selected');
                    const row = $(this).closest('tr');
                    const stockQty = parseFloat(selected.data('qty')) || 0;

                    row.find('.sku').val(selected.data('sku') || '');
                    row.find('.satuan').val(selected.data('satuan') || '');
                    row.find('.stock_dikartu').val(stockQty);
                    row.find('.stock_fisik').val(stockQty);
                    row.find('.selisih').val(0);
                });

                lastRow.find('.stock_fisik').on('input', function() {
                    const row = $(this).closest('tr');
                    const stockFisik = parseFloat($(this).val()) || 0;
                    const stockKartu = parseFloat(row.find('.stock_dikartu').val()) || 0;
                    row.find('.selisih').val((stockFisik - stockKartu).toFixed(2));
                });
            });

            $('#btnSaveOpname').on('click', function() {
                const tglStockOpname = $('#tglStockOpname').val();
                if (!tglStockOpname) {
                    alert('Tanggal Stock Opname harus diisi!');
                    return;
                }

                const items = [];
                $('#tableBody tr').each(function() {
                    const productInput = $(this).find('.product-name');
                    const selectStock = $(this).find('.select-stock');

                    let stockId = productInput.data('stock-id');
                    if (!stockId && selectStock.length) {
                        stockId = selectStock.val();
                    }

                    const selisih = parseFloat($(this).find('.selisih').val()) || 0;
                    const keterangan = $(this).find('.keterangan').val().trim();

                    if (stockId && selisih !== 0) {
                        items.push({
                            stock_id: stockId,
                            selisih: selisih,
                            keterangan: keterangan
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Tidak ada perubahan stock untuk disimpan!');
                    return;
                }

                if (!confirm(`Simpan ${items.length} penyesuaian stock?`)) {
                    return;
                }

                $.ajax({
                    url: '{{ route('stock.opname.save') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        adjustment_date: tglStockOpname,
                        items: items
                    },
                    success: function(data) {
                        if (data.success) {
                            alert('Stock opname berhasil disimpan!');
                            location.reload();
                        } else {
                            alert('Gagal menyimpan: ' + data.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat menyimpan data');
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
