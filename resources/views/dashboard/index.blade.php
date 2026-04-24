@extends('layouts.master')

@section('title', 'Dashboard')

@section('container')
    <section class="content-header">
        <h1>
            Dashboard
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <small>
                <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#modalMinStockAdj">
                    <i class="fa fa-sliders"></i> Pengaturan Min Stok Produk
                </button>
            </small>
            @endif
        </h1>
    </section>

    <section class="content">
        <!-- WIDGET: SUPPLIER-DEADLINE -->
        @if($urgentSuppliers->isNotEmpty())
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bell"></i>
                            Deadline PO Supplier Mendekat
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Deadline</th>
                                    <th>Sisa Hari</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($urgentSuppliers as $s)
                                    @php $days = \Carbon\Carbon::today()->diffInDays($s->next_deadline, false); @endphp
                                    <tr class="{{ $days === 0 ? 'danger' : ($days <= 1 ? 'warning' : '') }}">
                                        <td><strong>{{ $s->name }}</strong></td>
                                        <td>{{ $s->next_deadline->isoFormat('dddd, DD MMM YYYY') }}</td>
                                        <td>
                                            @if($days === 0)
                                                <span class="label label-danger">HARI INI</span>
                                            @else
                                                <span class="label label-warning">H-{{ $days }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('pembelian.create') }}" class="btn btn-xs btn-primary">
                                                <i class="fa fa-plus"></i> Buat PO
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- END WIDGET: SUPPLIER-DEADLINE -->
        <!-- WIDGET: NEAR-EXPIRY -->
        @if($nearExpiryStocks->isNotEmpty())
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-clock-o"></i>
                            Stok Mendekati Expired
                            <span class="badge bg-red">{{ $nearExpiryStocks->count() }}</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body table-responsive" style="max-height:300px;overflow-y:auto">
                        <table class="table table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kode</th>
                                    <th>Batch / SKU</th>
                                    <th>Qty Tersedia</th>
                                    <th>Tanggal Expired</th>
                                    <th>Sisa Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nearExpiryStocks as $stock)
                                    @php
                                        $daysLeft = (int) \Carbon\Carbon::today()->diffInDays($stock->expired_at, false);
                                    @endphp
                                    <tr class="{{ $daysLeft <= 7 ? 'danger' : ($daysLeft <= 14 ? 'warning' : '') }}">
                                        <td>{{ $stock->product?->name ?? '—' }}</td>
                                        <td>{{ $stock->product?->code ?? '—' }}</td>
                                        <td>{{ $stock->batch_number ?? $stock->sku ?? '—' }}</td>
                                        <td class="text-center">{{ $stock->qty_available }}</td>
                                        <td>{{ \Carbon\Carbon::parse($stock->expired_at)->format('d M Y') }}</td>
                                        <td class="text-center">
                                            @if($daysLeft <= 7)
                                                <span class="label label-danger">{{ $daysLeft }} hari</span>
                                            @elseif($daysLeft <= 14)
                                                <span class="label label-warning">{{ $daysLeft }} hari</span>
                                            @else
                                                <span class="label label-default">{{ $daysLeft }} hari</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- END WIDGET: NEAR-EXPIRY -->
        <div class="row">
            <div class="col-lg-2 col-xs-2">
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <p style="font-size:20px;">{{ $products }}</p>
                        <p>Jumlah Total products</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-archive"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-xs-2">
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <p style="font-size:20px;">{{ $stocks }}</p>
                        <p>Jumlah Total stocks</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-3">
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <p style="font-size:20px;">{{ $pembelianTerkirim }}</p>
                        <p>Jumlah Total Pembelian Terkirim</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-rocket"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-xs-2">
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <p style="font-size:20px;">{{ $penjualans }}</p>
                        <p>Jumlah Total Penjualan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-3">
                <div class="small-box bg-yellow-gradient">
                    <div class="inner">
                        <p style="font-size:20px;">@currency($totalRevenue)</p>
                        <p>Jumlah Total Pendapatan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-dollar"></i>
                    </div>
                </div>
            </div>
            {{-- <div class="col-lg-6 col-xs-6"> --}}
            {{-- <div id="bestOutlets"></div> --}}
            {{-- </div> --}}
            {{-- <div class="col-lg-12 col-xs-12"> --}}
            {{-- <h1 for="check-sliders">Sliders</h1> --}}
            {{-- @foreach ($sliders as $slider) --}}
            {{-- <img class="img-thumbnail" src="{{ asset($slider->pic) }}" alt=""> --}}
            {{-- @endforeach --}}
            {{-- </div> --}}
        </div>
        <div class="row">
            <div class="col-lg-6 col-xs-6">
                <div id="bestBuyProducts"></div>
            </div>
            <div class="col-lg-6 col-xs-6">
                <div id="bestBuySuppliers"></div>
                <hr />
            </div>
            <div class="col-lg-6 col-xs-6">
                <div id="salesGraph"></div>
                <hr />
            </div>
            <div class="col-lg-6 col-xs-6">
                <div id="monthlyRevenue"></div>
                <hr />
            </div>
            <div class="col-lg-12 col-xs-12">
                <div id="productGraph"></div>
                <hr />
            </div>
        </div>
    </section>

<!-- WIDGET: STOCK-ADJUSTMENT-MODAL -->
<div class="modal fade" id="modalMinStockAdj" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-sliders"></i> Pengaturan Perubahan Minimal Stok Produk
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Persentase Kenaikan (%)</label>
                            <input type="number" class="form-control" id="adjPercentage"
                                min="1" max="500" placeholder="Contoh: 20 untuk +20%">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Aktif Dari</label>
                            <input type="date" class="form-control" id="adjActiveFrom"
                                value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Aktif Sampai <small class="text-muted">(kosongkan = selamanya)</small></label>
                            <input type="date" class="form-control" id="adjActiveUntil">
                        </div>
                    </div>
                </div>
                <table id="tableAdjProducts" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="adjCheckAll"></th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Stok Saat Ini</th>
                            <th>Min Stok</th>
                            <th>Min Efektif (sekarang)</th>
                        </tr>
                    </thead>
                    <tbody id="adjProductBody">
                        @foreach($adjustmentProducts as $p)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="adj-product-check" value="{{ $p->id }}">
                                </td>
                                <td>{{ $p->code }}</td>
                                <td>{{ $p->name }}</td>
                                <td class="text-center">{{ $p->current_stock }}</td>
                                <td class="text-center">{{ $p->min_stock }}</td>
                                <td class="text-center">
                                    {{ $p->effective_min }}
                                    @if($p->effective_min > $p->min_stock)
                                        <span class="label label-info">+{{ $p->effective_min - $p->min_stock }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanAdj">
                    <i class="fa fa-save"></i> Simpan Adjustment
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END WIDGET: STOCK-ADJUSTMENT-MODAL -->
@endsection
@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.3.3/highcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.3.3/modules/exporting.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.3.3/modules/export-data.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/10.3.3/modules/accessibility.min.js"></script>
    <script>
        $.ajax({
            url: '/dashboard',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Use the data returned from the server to generate the graphs
                let bestBuyProducts = data.bestBuyProducts;
                let bestBuySuppliers = data.bestBuySuppliers;
                let salesGraph = data.salesGraph;
                let productGraph = data.productGraph;
                let monthlyRevenue = data.monthlyRevenue;
                console.table(bestBuyProducts);
                console.table(bestBuySuppliers);
                console.table(salesGraph);
                console.table(productGraph);
                console.table(monthlyRevenue);

                // 1. Grafik 10 Product Best Buy
                Highcharts.chart('bestBuyProducts', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '10 Product Best Buy'
                    },
                    xAxis: {
                        categories: bestBuyProducts.map(item => item.product.name)
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Quantity'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Quantity',
                        colorByPoint: true,
                        data: bestBuyProducts.map(item => item.total_qty).map(Number)
                    }]
                });

                // 2. Grafik 10 Outlet terbaik

                // 3. Grafik 10 Supplier Best Buy
                Highcharts.chart('bestBuySuppliers', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '10 Best Buy Suppliers'
                    },
                    xAxis: {
                        categories: bestBuySuppliers.map(item => item.supplier_name)
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Quantity'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Quantity',
                        data: bestBuySuppliers.map(item => item.total_qty).map(Number)
                    }]
                });

                // 4. Grafik Penjualan
                Highcharts.chart('salesGraph', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: 'Sales Graph'
                    },
                    xAxis: {
                        categories: salesGraph.map(item => item.date)
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Sales'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Sales',
                        colorByPoint: true,
                        data: salesGraph.map(item => item.total_sales).map(Number)
                    }]
                });

                // 5. Grafik Product
                Highcharts.chart('productGraph', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: 'Product Graph'
                    },
                    xAxis: {
                        categories: productGraph.map(item => item.product_name + ' (' + item.date + ')')
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Quantity'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Quantity',
                        colorByPoint: true,
                        data: productGraph.map(item => item.total_qty).map(Number)
                    }]
                });

                // 6. Grafik Pendapatan Perbulan
                Highcharts.chart('monthlyRevenue', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: 'Sales Graph Monthly'
                    },
                    xAxis: {
                        categories: monthlyRevenue.map(item => item.month + ' ' + item.year)
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Sales'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Sales',
                        colorByPoint: true,
                        data: monthlyRevenue.map(item => item.total).map(Number)
                    }]
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle any errors that occur
            }
        });
    </script>
    <script>
        // ---- Min Stock Adjustment Modal ----
        let adjTable = null;

        $('#modalMinStockAdj').on('shown.bs.modal', function () {
            if (!adjTable) {
                adjTable = $('#tableAdjProducts').DataTable({
                    pageLength: 10,
                    order: [[2, 'asc']],
                    columnDefs: [{ orderable: false, targets: [0] }],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ baris",
                        info: "Menampilkan _START_-_END_ dari _TOTAL_ produk",
                        paginate: { previous: "Prev", next: "Next" },
                        zeroRecords: "Tidak ada produk"
                    }
                });
            }
        });

        $(document).on('change', '#adjCheckAll', function () {
            const checked = $(this).prop('checked');
            if (adjTable) {
                adjTable.rows().nodes().each(function (node) {
                    $(node).find('.adj-product-check').prop('checked', checked);
                });
            }
        });

        $('#btnSimpanAdj').on('click', function () {
            const productIds = [];
            if (adjTable) {
                adjTable.rows().nodes().each(function (node) {
                    const $cb = $(node).find('.adj-product-check:checked');
                    if ($cb.length) productIds.push($cb.val());
                });
            }

            const pct        = parseInt($('#adjPercentage').val()) || 0;
            const activeFrom = $('#adjActiveFrom').val();
            const activeUntil = $('#adjActiveUntil').val();

            if (productIds.length === 0) { alert('Pilih minimal satu produk.'); return; }
            if (pct < 1)                  { alert('Persentase kenaikan minimal 1%.'); return; }
            if (!activeFrom)              { alert('Isi tanggal aktif dari.'); return; }

            $('#btnSimpanAdj').prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: '{{ route("product.minimum-adjustment.store") }}',
                method: 'POST',
                data: {
                    _token:                 '{{ csrf_token() }}',
                    product_ids:            productIds,
                    adjustment_percentage:  pct,
                    active_from:            activeFrom,
                    active_until:           activeUntil || null,
                },
                success: function (res) {
                    alert(res.message);
                    $('#modalMinStockAdj').modal('hide');
                    location.reload();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                    alert('Gagal: ' + msg);
                },
                complete: function () {
                    $('#btnSimpanAdj').prop('disabled', false).text('Simpan Adjustment');
                }
            });
        });
    </script>
@endsection
