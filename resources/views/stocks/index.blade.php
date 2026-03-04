@extends('layouts.master')
@section('title', 'Stocks')
@section('container')
    <section class="content-header">
        <h1>Data Stocks</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>SKU</td>
                                    <td>Code</td>
                                    <td>Product</td>
                                    <td>Harga Beli</td>
                                    <td>Stock Owner</td>
                                    <td>Qty Reserved</td>
                                    <td>Qty Available</td>
                                    <td>Created</td>
                                    <td>Expired</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stocks as $stock)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stock->sku }}</td>
                                        <td>{{ $stock->serial_number ?? $stock->product->code }}</td>
                                        <td>{{ $stock->product->name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-info btn-price-history"
                                                data-toggle="modal" data-target="#priceHistoryModal"
                                                data-id="{{ $stock->product_id }}">
                                                @currency($stock->harga_beli)
                                            </button>
                                        </td>
                                        <td>{{ $stock->ownerStock?->qty ?? 0 }}</td>
                                        <td>{{ $stock->qty_reserved ?? 0 }}</td>
                                        <td>{{ $stock->qty_available ?? 0 }}</td>
                                        <td>{{ $stock->created_at?->format('h:i a / d-M-Y') }}</td>
                                        <td>{{ $stock->expired_at?->format('d-M-Y') }}</td>
                                        <td>
                                            <span
                                                class="label label-{{ $stock->status == 'available' ? 'success' : 'warning' }}">
                                                {{ $stock->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-primary" data-toggle="modal"
                                                data-target="#historyModal{{ $stock->id }}">
                                                <i class="fa fa-history"></i> History
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Price History Modal -->
                        <div class="modal fade" id="priceHistoryModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Price History (Harga Beli)</h4>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>User</th>
                                                    <th>Change</th>
                                                </tr>
                                            </thead>
                                            <tbody id="priceHistoryBody">
                                                <tr>
                                                    <td colspan="3" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#priceHistoryModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var modal = $(this);
                modal.find('#priceHistoryBody').html(
                    '<tr><td colspan="3" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: '/product/' + id + '/price-history',
                    method: 'GET',
                    success: function(res) {
                        var rows = '';
                        if (res.data && res.data.length) {
                            res.data.forEach(function(item) {
                                var change = item.event === 'created' ?
                                    'Created → ' + Number(item.new).toLocaleString() :
                                    Number(item.old).toLocaleString() + ' → ' + Number(
                                        item.new).toLocaleString();
                                rows += '<tr><td>' + item.date + '</td><td>' + item
                                    .user + '</td><td>' + change + '</td></tr>';
                            });
                        } else {
                            rows =
                                '<tr><td colspan="3" class="text-center">No changes found.</td></tr>';
                        }
                        modal.find('#priceHistoryBody').html(rows);
                    },
                    error: function() {
                        modal.find('#priceHistoryBody').html(
                            '<tr><td colspan="3" class="text-center text-danger">Error loading data.</td></tr>'
                        );
                    }
                });
            });
        });
    </script>
@endsection
