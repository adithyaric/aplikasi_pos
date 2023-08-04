<table>
    <thead>
        <tr>
            <th colspan="3">Pendapatan</th>
        </tr>
        @foreach ($kas as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td></td>
                <td>@currency($item->penjualan->sum('total'))</td>
            </tr>
        @endforeach
        <tr>
            <th>Total Pendapatan</th>
            <td></td>
            <td>@currency($total_penjualan)</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($pengeluaran as $item)
            <tr>
                <td>{{ $item->category->name }}</td>
                <td>@currency($item->kas)</td>
                <td></td>
            </tr>
        @endforeach
        <tr>
            <td>Pembelian</td>
            <td>@currency($total_pembelian)</td>
            <td></td>
        </tr>
        <tr>
            <th>Laba/Rugi</th>
            <td></td>
            <td>@currency($laba_rugi)</td>
        </tr>
    </tbody>
</table>
