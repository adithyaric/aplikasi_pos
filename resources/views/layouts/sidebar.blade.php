<aside class="main-sidebar">
    <section class="sidebar">

        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('img/logo.png') }}" class="img-circle" alt="User Image"><br>
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->name }}</p>
                <p>POS</p>
            </div>
        </div>

    <ul class="sidebar-menu">
        <li class="{{ request()->is('dashboard*') ? 'active' : '' }}"><a href="/dashboard"><i class="fa fa-tachometer"></i><span>Dashboard</span></a></li>
        @if (auth()->user()->role != 'kasir')
            <li class="{{ request()->is('setting*') ? 'active' : '' }}"><a href="/setting"><i class="fa fa-gear"></i><span>Setting</span></a></li>
            <li class="treeview {{ request()->is('admin*') || request()->is('customer*') || request()->is('salesman*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-users"></i><span>Users</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('admin*') ? 'active' : '' }}"><a href="/admin"><i class="fa fa-users"></i><span>Admins</span></a></li>
                    <li class="{{ request()->is('customer*') ? 'active' : '' }}"><a href="/customer"><i class="fa fa-users"></i><span>Customers</span></a></li>
                    <li class="{{ request()->is('salesman*') ? 'active' : '' }}"><a href="/salesman"><i class="fa fa-user-secret"></i><span>Salesmans</span></a></li>
                </ul>
            </li>
            <li class="treeview {{ request()->is('category-product*') || request()->is('product*') || request()->is('stock*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-archive"></i><span>Products</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('category-product*') ? 'active' : '' }}"><a href="/category-product"><i class="fa fa-tags"></i><span>Category</span></a></li>
                    <li class="{{ request()->is('product*') ? 'active' : '' }}"><a href="/product"><i class="fa fa-archive"></i><span>Products</span></a></li>
                    {{-- <li class="{{ request()->is('stock*') ? 'active' : '' }}"><a href="/stock"><i class="fa fa-cubes"></i><span>Stocks</span></a></li> --}}
                </ul>
            </li>
            <li class="treeview {{ request()->is('category-pengeluaran*') || request()->is('pengeluaran*') || request()->is('pembelian*') || request()->is('penjualan*') || request()->is('refund*')
                || request()->is('request-orders*')
                || request()->is('picking-lists*')
                || request()->is('delivery-orders*')
            ? 'active' : '' }}">
                <a href="#"><i class="fa fa-exchange"></i><span>Transactions</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    {{-- <li class="{{ request()->is('category-pengeluaran*') ? 'active' : '' }}"><a href="/category-pengeluaran"><i class="fa fa-tags"></i><span>Category Pengeluaran</span></a></li> --}}
                    {{-- <li class="{{ request()->is('pengeluaran*') ? 'active' : '' }}"><a href="/pengeluaran"><i class="fa fa-folder-open"></i><span>Pengeluaran</span></a></li> --}}
                    <li class="{{ request()->is('pembelian*') ? 'active' : '' }}"><a href="/pembelian"><i class="fa fa-cube "></i><span>Gudang Minta Supplier</span></a></li>
                    <li class="{{ request()->is('request-orders*') ? 'active' : '' }}"><a href="/request-orders"><i class="fa fa-cube"></i><span>Outlet Minta Gudang</span></a></li>
                    <li class="{{ request()->is('picking-lists*') ? 'active' : '' }}"><a href="/picking-lists"><i class="fa fa-cube"></i><span>PICKING & PACKING</span></a></li>
                    <li class="{{ request()->is('delivery-orders*') ? 'active' : '' }}"><a href="/delivery-orders"><i class="fa fa-cube"></i><span>OUTBOUND (Kirim ke Outlet)</span></a></li>
                    {{-- <li class="{{ request()->is('penjualan') ? 'active' : '' }}"><a href="/penjualan"><i class="fa fa-shopping-cart"></i><span>Penjualan</span></a></li> --}}
                    {{-- <li class="{{ request()->is('penjualan-marketplace') ? 'active' : '' }}"><a href="/penjualan-marketplace"><i class="fa fa-shopping-cart"></i><span>Penjualan Marketplace</span></a></li> --}}
                    {{-- <li class="{{ in_array(Route::currentRouteName(), ['refund.index', 'refund.show', 'refund.create', 'refund.edit']) ? 'active' : '' }}"><a href="/refund"><i class="fa fa-refresh"></i><span>Refund/Return Order</span></a></li> --}}
                    {{-- <li class="{{ request()->is('refundPembelian*') ? 'active' : '' }}"><a href="/refundPembelian"><i class="fa fa-refresh"></i><span>Refund/Return Pembelian</span></a></li> --}}
                </ul>
            </li>
        @endif
        {{-- <li class="{{ request()->is('penjualan/create') || Route::currentRouteName() == 'outlet.show'  ? 'active' : '' }}"><a href="/penjualan/create"><i class="fa fa-calculator"></i><span>POS</span></a></li> --}}
        @if (auth()->user()->role == 'superadmin')
            <li class="{{ request()->is('kas*') ? 'active' : '' }}"><a href="/kas"><i class="fa fa-bank"></i><span>Kas</span></a></li>
            <li class="{{ in_array(Route::currentRouteName(), ['outlet.index', 'outlet.create', 'outlet.edit']) ? 'active' : '' }}"><a href="/outlet"><i class="fa fa-home"></i><span>Outlets</span></a></li>
            {{-- <li class="{{ request()->is('voucher*') ? 'active' : '' }}"><a href="/voucher"><i class="fa fa-newspaper-o"></i><span>Vouchers</span></a></li> --}}
            {{-- <li class="{{ request()->is('slider*') ? 'active' : '' }}"><a href="/slider"><i class="fa fa-image"></i><span>Sliders</span></a></li> --}}
        @endif
        <li class="treeview {{ request()->is('laporan*') ? 'active' : '' }}">
            <a href="#">
                <i class="fa fa-file-excel-o"></i><span>Laporan</span><i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li class="{{ request()->is('laporan*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.index') }}"><i class="fa fa-file-excel-o"></i><span>Laporan</span></a>
                </li>
                @if (auth()->user()->role != 'kasir')
                <li class="{{ request()->is('laporan/stock*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.stock') }}"><i class="fa fa-file-text-o"></i><span> - Stock</span></a>
                </li>
                <li class="{{ request()->is('laporan/pengeluaran*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.pengeluaran') }}"><i class="fa fa-file-text-o"></i><span> - Pengeluaran</span></a>
                </li>
                <li class="{{ request()->is('laporan/labarugi*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.labarugi') }}"><i class="fa fa-file-text-o"></i><span> - Laba Rugi</span></a>
                </li>
                @endif
            </ul>
        </li>
    </ul>
    </section>
</aside>
