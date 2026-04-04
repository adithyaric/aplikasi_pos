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
                                        <th>Available Qty</th>
                                        <th>Qty Request</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][product_id]" class="form-control product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-available="{{ $product->stocks_qty_available }}">
                                                        {{ $product->code }} - {{ $product->name }} : {{ $product->stocks_qty_available }}
                                                    </option>
                                                @endforeach
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
        let products = @json($products);
        let rowIndex = {{ isset($requestOrder) ? count($requestOrder->items) : 1 }};

function populateProductSelect($select, selectedId = null) {
    $select.empty().append('<option value="">Select Product</option>');
    $.each(products, function(index, product) {
        // Calculate available qty from loaded stocks (if any)
        let available = 0;
        if (product.stocks && product.stocks.length) {
            available = product.stocks.reduce((sum, stock) => sum + (stock.qty_available || 0), 0);
        }
        let option = $('<option>', {
            value: product.id,
            'data-available': available,
            text: product.code + ' - ' + product.name + ' : ' + available
        });
        $select.append(option);
    });
    if (selectedId) {
        $select.val(selectedId);
    }
    $select.trigger('change');
}

        $(document).ready(function() {
            // Initialize all existing product-select with select2 and width 100%
            $('.product-select').each(function() {
                let $select = $(this);
                let currentVal = $select.val();
                populateProductSelect($select, currentVal);
                // $select.select2({ width: '100%' });
            });
        });

        $(document).on('change', '.product-select', function() {
            let $row = $(this).closest('tr');
            let available = $(this).find(':selected').data('available') || 0;
            $row.find('.available-qty').text(available);
            $row.find('input[name*="qty_requested"]').attr('max', available);
        });

        $('#add-row').click(function() {
            let newRow = `
        <tr class="item-row">
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control product-select" required style="width:100%;">
                    <option value="">Select Product</option>
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
            let $newSelect = $('#items-table tbody tr:last .product-select');
            populateProductSelect($newSelect);
            //$newSelect.select2({ width: '100%' });
            rowIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    </script>
@endsection