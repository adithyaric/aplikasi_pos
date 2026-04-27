<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body>
    @include('exports.pdf._header')
    <div class="report-title">LAPORAN PENERIMAAN BARANG</div>
    <div class="report-periode">Periode: {{ \Carbon\Carbon::parse($mulai)->isoFormat('DD MMMM YYYY') }} &ndash;
        {{ \Carbon\Carbon::parse($selesai)->isoFormat('DD MMMM YYYY') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:8%">Tanggal</th>
                <th style="width:11%">No Dokumen</th>
                <th style="width:10%">No PO</th>
                <th style="width:10%">Supplier</th>
                <th style="width:7%">Kode Barang</th>
                <th style="width:14%">Nama Barang</th>
                <th style="width:10%">Batch</th>
                <th style="width:8%">Expired</th>
                <th style="width:5%">Qty</th>
                <th style="width:5%">Qty Diterima</th>
                <th style="width:5%">Satuan</th>
                <th style="width:8%">Konversi</th>
                <th style="width:6%">Kondisi</th>
                <th style="width:8%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $i => $s)
                <tr class="{{ $i % 2 == 1 ? 'alt' : '' }}">
                    <td class="tc">{{ $i + 1 }}</td>
                    <td class="tc">{{ $s->created_at->isoFormat('DD MMM YYYY') }}</td>
                    <td>{{ $s->pembelian?->code_gr ?? ($s->pembelian?->code ?? '-') }}</td>
                    <td>{{ $s->pembelian?->code ?? '-' }}</td>
                    <td>{{ $s->pembelian?->supplier?->name ?? '-' }}</td>
                    <td>{{ $s->product?->code ?? '-' }}</td>
                    <td>{{ $s->product?->name ?? '-' }}</td>
                    <td>{{ $s->sku ?? '-' }}</td>
                    <td class="tc">
                        {{ $s->expired_date ? \Carbon\Carbon::parse($s->expired_date)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="tc">{{ $s->qty ?? 0 }}</td>
                    <td class="tc">{{ $s->qty_diterima ?? 0 }}</td>
                    <td class="tc">{{ $s->product?->satuan ?? 'PCS' }}</td>
                    <td class="tc">
                        @php
                            $kQty = $s->product?->konversi_qty;
                            $kUnit = $s->product?->satuan_besar;
                            $sQty = $s->qty ?? 0;
                        @endphp
                        {{ ($kQty > 0 && $kUnit) ? round($sQty / $kQty).' '.$kUnit : '-' }}
                    </td>
                    <td class="tc">Baik</td>
                    <td>{{ $s->pembelian?->notes ?? 'Pembelian' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="tc">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
