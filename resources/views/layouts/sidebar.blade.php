<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">PB IDM<sup>VB</sup></div>
    </a>

    @php
        $sub_url = Request::segment(1);
    @endphp

    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <div class="sidebar-heading mt-2" style="padding-top: 8px">
        HOME
    </div>
    <li class="nav-item @if($sub_url == 'home') active @endif">
        <a class="nav-link" href="{{ url('/home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Home</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
    <div class="sidebar-heading mt-2" style="padding-top: 8px">
        Menu
    </div>
    <li class="nav-item @if($sub_url == 'monitoring-web-service') active @endif">
        <a class="nav-link" href="{{ url('/monitoring-web-service') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Monitoring Web Service</span></a>
    </li>
    <li class="nav-item @if($sub_url == 'rtt-idm') active @endif">
        <a class="nav-link" href="{{ url('/rtt-idm') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>RTT IDM</span></a>
    </li>
    <li class="nav-item @if($sub_url == 'dspb-roti') active @endif">
        <a class="nav-link" href="{{ url('/dspb-roti') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>DSPB ROTI</span></a>
    </li>
    <li class="nav-item @if($sub_url == 'history-produk') active @endif">
        <a class="nav-link" href="{{ url('/history-produk') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>History Produk</span></a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <!-- <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div> -->
</ul>
