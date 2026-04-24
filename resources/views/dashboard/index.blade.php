@extends('layouts.master')

@section('title', 'Dashboard')

@section('container')
    <section class="content-header">
        <h1>
            Dashboard
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
@endsection
