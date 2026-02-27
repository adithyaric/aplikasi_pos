@extends('layouts.master')

@section('title', 'Tambah Request Order')

@section('container')
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tambah Request Order</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{ route('request-orders.store') }}" method="POST">
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label>Owner/Outlet <span class="text-danger">*</span></label>
                                <select name="owner_id" class="form-control select2" required>
                                    <option value="">Select Outlet</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Request Date <span class="text-danger">*</span></label>
                                <input type="date" name="request_date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>

                            <hr>
                            <h4>Select Products & SKU</h4>
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th width="150">Available Qty</th>
                                        <th width="150">Qty Request</th>
                                        <th width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][product_id]" class="form-control product-select"
                                                required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[0][stock_id]" class="form-control sku-select" required
                                                disabled>
                                                <option value="">Select SKU</option>
                                            </select>
                                        </td>
                                        <td class="available-qty">-</td>
                                        <td>
                                            <input type="number" name="items[0][qty_requested]" class="form-control"
                                                min="1" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                    class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success" id="add-row"><i class="fa fa-plus"></i> Add
                                Product</button>
                        </div>

                        <div class="box-footer">
                            <a href="{{ route('request-orders.index') }}" class="btn btn-default">Kembali</a>
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
        let rowIndex = 1;

        $(document).on('change', '.product-select', function() {
            const row = $(this).closest('tr');
            const productId = $(this).val();
            const skuSelect = row.find('.sku-select');

            skuSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);
            row.find('.available-qty').text('-');

            if (!productId) return;

            $.get(`/api/stocks/by-product/${productId}`, function(stocks) {
                skuSelect.empty().append('<option value="">Select SKU</option>');

                if (stocks.length === 0) {
                    skuSelect.append('<option value="">No stock available</option>');
                    return;
                }

                stocks.forEach(stock => {
                    const label =
                        `${stock.sku} (Qty: ${stock.qty_available}, Exp: ${stock.expired_at || 'N/A'})`;
                    skuSelect.append(
                        `<option value="${stock.id}" data-qty="${stock.qty_available}">${label}</option>`
                        );
                });

                skuSelect.prop('disabled', false);
            });
        });

        $(document).on('change', '.sku-select', function() {
            const row = $(this).closest('tr');
            const selectedOption = $(this).find(':selected');
            const availableQty = selectedOption.data('qty') || 0;

            row.find('.available-qty').text(availableQty);
            row.find('input[name*="qty_requested"]').attr('max', availableQty);
        });

        $('#add-row').click(function() {
            const newRow = `
        <tr class="item-row">
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][stock_id]" class="form-control sku-select" required disabled>
                    <option value="">Select SKU</option>
                </select>
            </td>
            <td class="available-qty">-</td>
            <td>
                <input type="number" name="items[${rowIndex}][qty_requested]" class="form-control" min="1" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;
            $('#items-table tbody').append(newRow);
            rowIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    </script>
@endsection
