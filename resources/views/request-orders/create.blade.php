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
                            <div class="table-responsive text-nowrap">
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
                                                <select name="items[0][product_id]" class="form-control product-select select2"
                                                    required>
                                                    <option value="">Select Product </option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-available="{{ $product->total_available }}">
                                                            {{ $product->code }} - {{ $product->name }} : {{ $product->total_available }}
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
                            </div>
                            <button type="button" class="btn btn-success" id="add-row"><i class="fa fa-plus"></i> Add Product</button>

                            <hr>
                            <h4>Catatan Tambahan <small class="text-muted">(opsional — tidak berelasi ke produk, hanya catatan)</small></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="notes-table">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th style="width:120px">Qty</th>
                                            <th>Nama PJ</th>
                                            <th style="width:60px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="notes-tbody">
                                        {{-- rows added dynamically --}}
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-default btn-sm" id="add-note-row">
                                <i class="fa fa-plus"></i> Tambah Catatan
                            </button>
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
        let available = product.total_available || 0;
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
                $select.select2({ width: '100%' });
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
            $newSelect.select2({ width: '100%' });
            rowIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
            }
        });

        // ---- Catatan Tambahan repeater ----
        let noteIndex = 0;

        function addNoteRow() {
            const row = `
                <tr class="note-row">
                    <td><input type="text" name="extra_notes[${noteIndex}][kategori]" class="form-control" placeholder="Kategori" required></td>
                    <td><input type="number" name="extra_notes[${noteIndex}][qty]" class="form-control" min="0" value="0" required></td>
                    <td><input type="text" name="extra_notes[${noteIndex}][nama_pj]" class="form-control" placeholder="Nama PJ"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-note-row"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;
            $('#notes-tbody').append(row);
            noteIndex++;
        }

        $('#add-note-row').on('click', addNoteRow);

        $(document).on('click', '.remove-note-row', function() {
            $(this).closest('tr').remove();
        });
    </script>
@endsection