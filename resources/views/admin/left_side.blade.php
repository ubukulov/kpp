<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::guard('admin')->user()->full_name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->is('admin/dashboard')) active @endif">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Панель администратора
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-copy"></i>
                        <p>
                            Справочники
                            <i class="fas fa-angle-left right"></i>
                            <span class="badge badge-info right">6</span>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('position.index') }}" class="nav-link @if(request()->is('admin/position*')) active @endif">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Список должностей</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('company.index') }}" class="nav-link @if(request()->is('admin/company*')) active @endif">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Список компании</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employee.index') }}" class="nav-link @if(request()->is('admin/employee*')) active @endif">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Список пользователей
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.drivers.index') }}" class="nav-link @if(request()->is('admin/drivers*')) active @endif">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Список водителей
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="nav-link @if(request()->is('admin/reports*')) active @endif">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Отчёты
                        </p>
                    </a>
                </li>
                {{--<li class="nav-item">
                    <a href="{{ route('admin.whatsapp.index') }}" class="nav-link @if(request()->is('admin/sending*')) active @endif">
                        <i class="nav-icon fab fa-whatsapp"></i>
                        <p>
                            Рассылка
                        </p>
                    </a>
                </li>--}}
                <li class="nav-item">
                    <a href="{{ route('admin.logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Выйти из админки
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
