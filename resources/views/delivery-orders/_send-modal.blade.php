{{-- $do: DeliveryOrder model with items.product loaded --}}
<div class="modal fade" id="sendModal{{ $do->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('delivery-orders.send', $do->id) }}"
                method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Konfirmasi Pengiriman — {{ $do->code }}</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted"><small>Periksa dan sesuaikan qty yang benar-benar dikirim.</small></p>
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th class="text-center">Qty Pick</th>
                                <th class="text-center" style="width:110px">Qty Kirim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($do->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->sku ?? '-' }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td>
                                    <input type="number"
                                        name="items[{{ $item->id }}][qty_sent]"
                                        class="form-control input-sm text-center"
                                        value="{{ $item->qty }}"
                                        min="0" max="{{ $item->qty }}" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="form-group">
                        <label>Upload Dispatch Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Send this delivery order?')">Confirm Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
