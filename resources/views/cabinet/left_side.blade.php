<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            {{--<div class="image">
                <img src="/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>--}}
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->company->short_ru_name }}</a>
                <a href="#" class="d-block">{{ Auth::user()->full_name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                {{--<li class="nav-item">
                    <a href="{{ route('cabinet') }}" class="nav-link @if(request()->is('cabinet')) active @endif">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Панель клиента
                        </p>
                    </a>
                </li>--}}
                @role('otdel-kadrov')
                <li class="nav-item">
                    <a href="{{ route('cabinet.employees.index') }}" class="nav-link  @if(request()->is('cabinet/employees*')) active @endif">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Список сотрудников
                        </p>
                    </a>
                </li>
                @endrole
                @role('kpp-direktor')
                <li class="nav-item">
                    <a href="{{ route('cabinet.white-car-list.index') }}" class="nav-link @if(request()->is('cabinet/white-car-list*')) active @endif">
                        <i class="nav-icon fas fa-car"></i>
                        <p>
                            Машины (белые)
                        </p>
                    </a>
                </li>
                @endrole
                <li class="nav-item">
                    <a href="{{ route('cabinet.report.index') }}" class="nav-link @if(request()->is('cabinet/reports*')) active @endif">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Отчёты
                        </p>
                    </a>
                </li>

                @if(Auth::user()->company->id == 2)
                    <li class="nav-item">
                        <a href="{{ route('cabinet.kpp.samsung') }}" class="nav-link @if(request()->is('cabinet/kpp*')) active @endif">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                КПП
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('cabinet.barcode.samsung') }}" class="nav-link @if(request()->is('cabinet/samsung/barcode*')) active @endif">
                            <i class="nav-icon fa fa-barcode"></i>
                            <p>
                                Штрих-код
                            </p>
                        </a>
                    </li>
                @endif

                @if(\Gate::allows('create-permit'))
                <li class="nav-item">
                    <a href="{{ route('cabinet.permits.index') }}" class="nav-link @if(request()->is('cabinet/permits*')) active @endif">
                        <i class="nav-icon fas fa-align-justify"></i>
                        <p>
                            Оформление пропуска
                        </p>
                    </a>
                </li>
                @endif

                {{--<li class="nav-item">
                    <a href="{{ route('cabinet.service.index') }}" class="nav-link @if(request()->is('cabinet/services*')) active @endif">
                        <i class="nav-icon fas fa-poll"></i>
                        <p>
                            Услуги
                        </p>
                    </a>
                </li>--}}
                {{--<li class="nav-item">
                    <a href="{{ route('employees.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-car-side"></i>
                        <p>
                            Заказать машину
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employees.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Отследить груз
                        </p>
                    </a>
                </li>--}}
                <li class="nav-item">
                    <a href="{{ route('logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Выйти из кабинета
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
