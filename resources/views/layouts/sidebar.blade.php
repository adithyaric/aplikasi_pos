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
            <li class="{{ request()->is('dashboard*') ? 'active' : '' }}">
                <a href="/dashboard">
                    <i class="fa fa-tachometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ request()->is('admin*') ? 'active' : '' }}">
                <a href="/admin">
                    <i class="fa fa-users"></i>
                    <span>Admin</span>
                </a>
            </li>
            <li class="{{ request()->is('customer*') ? 'active' : '' }}">
                <a href="/customer">
                    <i class="fa fa-users"></i>
                    <span>Customer</span>
                </a>
            </li>
            <li class="{{ request()->is('bank*') ? 'active' : '' }}">
                <a href="/bank">
                    <i class="fa fa-bank"></i>
                    <span>Bank</span>
                </a>
            </li>
            <li class="{{ request()->is('outlet*') ? 'active' : '' }}">
                <a href="/outlet">
                    <i class="fa fa-home"></i>
                    <span>Outlet</span>
                </a>
            </li>
            <li class="{{ request()->is('supplier*') ? 'active' : '' }}">
                <a href="/supplier">
                    <i class="fa fa-dropbox"></i>
                    <span>Supplier</span>
                </a>
            </li>
            <li class="{{ request()->is('category*') ? 'active' : '' }}">
                <a href="/category">
                    <i class="fa fa-tags"></i>
                    <span>Category</span>
                </a>
            </li>
        </ul>
    </section>
</aside>
