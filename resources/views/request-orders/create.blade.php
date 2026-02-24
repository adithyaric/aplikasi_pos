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
                            {{-- Code is auto-generated, not shown in form --}}
                            <div class="form-group">
                                <label>Owner (Outlet)</label>
                                <select class="form-control select2" name="owner_id" data-placeholder="Pilih Outlet"
                                    style="width: 100%;" required>
                                    <option value="" selected disabled>Pilih Outlet</option>
                                    {{-- Make sure $outlets is passed from controller --}}
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}"
                                            {{ old('owner_id') || auth()->user()->outlet_id == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <div class="invalid-feedback text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Tanggal Request</label>
                                <input type="date" class="form-control" name="request_date"
                                    value="{{ old('request_date', date('Y-m-d')) }}" required>
                                @error('request_date')
                                    <div class="invalid-feedback text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Catatan Umum</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>
                            <h4>Item yang diminta</h4>
                            <table class="table table-bordered table-striped" id="items-table">
                                <thead>
                                    <tr>
                                        <td>Produk</td>
                                        <td>Qty</td>
                                        <td>Catatan Item</td>
                                        <td>Aksi</td>
                                    </tr>
                                </thead>
                                <tbody id="item-repeater">
                                    <tr>
                                        <td>
                                            <select class="form-control select2 product" name="items[0][product_id]"
                                                data-placeholder="Pilih Produk" style="width:100%" required>
                                                <option value="" disabled selected>Pilih Produk</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-stock="{{ $product->stocks()->sum('qty_available') }}">
                                                        {{ $product->name }} :
                                                        [{{ $product->stocks()->sum('qty_available') }}]
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control qty" name="items[0][qty_requested]"
                                                required value="1" min="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[0][notes]"
                                                placeholder="Catatan item (opsional)">
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="removeItem(this)"
                                                type="button">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <button class="btn btn-sm btn-primary" onclick="addItem()" type="button">Tambah Item</button>
                        </div><!-- /.box-body -->

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
        let itemIndex = 1; // start from 1 because first row is index 0

        function addItem() {
            let template = `
        <tr>
            <td>
                <select class="form-control select2 product" name="items[${itemIndex}][product_id]" data-placeholder="Pilih Produk" style="width:100%" required>
                    <option value="" disabled selected>Pilih Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stocks()->sum('qty_available') }}">
                            {{ $product->name }} : [{{ $product->stocks()->sum('qty_available') }}]
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control qty" name="items[${itemIndex}][qty_requested]" required value="1" min="1">
            </td>
            <td>
                <input type="text" class="form-control" name="items[${itemIndex}][notes]" placeholder="Catatan item (opsional)">
            </td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="removeItem(this)" type="button">Hapus</button>
            </td>
        </tr>`;
            $('#item-repeater').append(template);
            var $newSelect = $('#item-repeater .select2').last();
            $newSelect.select2();
            setMaxQty($newSelect); // set max if option is pre-selected (none by default)
            itemIndex++;
        }

        function removeItem(button) {
            if ($('#item-repeater tr').length > 1) {
                $(button).closest('tr').remove();
            } else {
                alert('Minimal satu item harus diisi');
            }
        }

        // Initialize select2 for existing rows
        $(document).ready(function() {
            $('.select2').select2();
            $('.product').each(function() {
                setMaxQty(this);
            });
        });

        function setMaxQty(selectElement) {
            var $select = $(selectElement);
            var $row = $select.closest('tr');
            var stock = $select.find(':selected').data('stock');
            var $qty = $row.find('.qty');
            if (stock !== undefined) {
                $qty.attr('max', stock);
                // Optional: if current value > stock, adjust it
                if (parseInt($qty.val()) > stock) {
                    $qty.val(stock);
                }
            } else {
                $qty.removeAttr('max');
            }
        }

        // On product change
        $(document).on('change', '.product', function() {
            setMaxQty(this);
        });
    </script>
@endsection
