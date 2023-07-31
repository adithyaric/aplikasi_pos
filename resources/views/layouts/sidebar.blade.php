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
        <li class="treeview {{ request()->is('admin*') || request()->is('customer*') || request()->is('supplier*') ? 'active' : '' }}">
            <a href="#"><i class="fa fa-users"></i><span>Users</span><i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="{{ request()->is('admin*') ? 'active' : '' }}"><a href="/admin"><i class="fa fa-users"></i><span>Admins</span></a></li>
                <li class="{{ request()->is('customer*') ? 'active' : '' }}"><a href="/customer"><i class="fa fa-users"></i><span>Customers</span></a></li>
                <li class="{{ request()->is('supplier*') ? 'active' : '' }}"><a href="/supplier"><i class="fa fa-truck"></i><span>Suppliers</span></a></li>
            </ul>
        </li>
        <li class="treeview {{ request()->is('category*') || request()->is('product*') || request()->is('stock*') ? 'active' : '' }}">
            <a href="#"><i class="fa fa-archive"></i><span>Products</span><i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="{{ request()->is('category*') ? 'active' : '' }}"><a href="/category"><i class="fa fa-tags"></i><span>Category</span></a></li>
                <li class="{{ request()->is('product*') ? 'active' : '' }}"><a href="/product"><i class="fa fa-archive"></i><span>Products</span></a></li>
                <li class="{{ request()->is('stock*') ? 'active' : '' }}"><a href="/stock"><i class="fa fa-cubes"></i><span>Stocks</span></a></li>
            </ul>
        </li>
        <li class="treeview {{ request()->is('pengeluaran*') || request()->is('pembelian*') || request()->is('penjualan') || request()->is('refund*') ? 'active' : '' }}">
            <a href="#"><i class="fa fa-exchange"></i><span>Transactions</span><i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="{{ request()->is('pengeluaran*') ? 'active' : '' }}"><a href="/pengeluaran"><i class="fa fa-folder-open"></i><span>Pengeluaran</span></a></li>
                <li class="{{ request()->is('pembelian*') ? 'active' : '' }}"><a href="/pembelian"><i class="fa fa-cube "></i><span>Pembelian</span></a></li>
                <li class="{{ request()->is('penjualan') ? 'active' : '' }}"><a href="/penjualan"><i class="fa fa-shopping-cart"></i><span>Penjualan</span></a></li>
                <li class="{{ request()->is('refund*') ? 'active' : '' }}"><a href="/refund"><i class="fa fa-refresh"></i><span>Refund/Return Order</span></a></li>
            </ul>
        </li>
        <li class="{{ request()->is('penjualan/create') ? 'active' : '' }}"><a href="/penjualan/create"><i class="fa fa-calculator"></i><span>POS</span></a></li>
        <li class="{{ request()->is('bank*') ? 'active' : '' }}"><a href="/bank"><i class="fa fa-bank"></i><span>Bank</span></a></li>
        <li class="{{ request()->is('outlet*') ? 'active' : '' }}"><a href="/outlet"><i class="fa fa-home"></i><span>Outlets</span></a></li>
        <li class="{{ request()->is('voucher*') ? 'active' : '' }}"><a href="/voucher"><i class="fa fa-newspaper-o"></i><span>Vouchers</span></a></li>
        <li class="{{ request()->is('slider*') ? 'active' : '' }}"><a href="/slider"><i class="fa fa-image"></i><span>Sliders</span></a></li>
    </ul>
    </section>
</aside>
