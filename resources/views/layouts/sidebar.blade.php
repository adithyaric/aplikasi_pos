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
            <li class=""><a href="/dashboard"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a></li>
        </ul>
    </section>
</aside>
