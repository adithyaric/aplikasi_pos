<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body>
    @include('exports.pdf._header')
    <div class="report-title">LAPORAN STOK BARANG</div>
    <div class="report-periode">Per Tanggal: {{ now()->isoFormat('DD MMMM YYYY') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:8%">Kode Barang</th>
                <th style="width:16%">Nama Barang</th>
                <th style="width:12%">Batch</th>
                <th style="width:9%">Expired Date</th>
                <th style="width:8%">Kategori</th>
                <th style="width:5%">Satuan</th>
                <th style="width:6%">Stok</th>
                <th style="width:6%">Min Stok</th>
                <th style="width:6%">Selisih</th>
                <th style="width:7%">Status Stok</th>
                <th style="width:7%">Expired</th>
                <th style="width:9%">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $i => $s)
                @php
                    $minStok = $s->product?->min_stock ?? 0;
                    $selisih = ($s->qty ?? 0) - $minStok;
                    $statusStok = ($s->qty ?? 0) > $minStok ? 'Aman' : (($s->qty ?? 0) > 0 ? 'Kritis' : 'Habis');
                    $statusExp =
                        $s->expired_date && \Carbon\Carbon::parse($s->expired_date)->isPast() ? 'Expired' : '-';
                @endphp
                <tr class="{{ $i % 2 == 1 ? 'alt' : '' }}">
                    <td class="tc">{{ $i + 1 }}</td>
                    <td>{{ $s->product?->code ?? '-' }}</td>
                    <td>{{ $s->product?->name ?? '-' }}</td>
                    <td>{{ $s->sku ?? '-' }}</td>
                    <td class="tc">
                        {{ $s->expired_date ? \Carbon\Carbon::parse($s->expired_date)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $s->product?->category?->name ?? '-' }}</td>
                    <td class="tc">{{ $s->product?->satuan ?? 'PCS' }}</td>
                    <td class="tc">{{ $s->qty ?? 0 }}</td>
                    <td class="tc">{{ $minStok }}</td>
                    <td class="tc">{{ ($selisih >= 0 ? '+' : '') . $selisih }}</td>
                    <td class="tc">{{ $statusStok }}</td>
                    <td class="tc">{{ $statusExp }}</td>
                    <td>{{ $s->location ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="tc">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
