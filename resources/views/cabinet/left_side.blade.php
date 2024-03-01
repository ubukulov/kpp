@include('cabinet.nav')
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

                @role('buhgalteriya')
                    <li class="nav-item">
                        <a href="{{ route('cabinet.report.index') }}" class="nav-link @if(request()->is('cabinet/reports*')) active @endif">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Отчёты
                            </p>
                        </a>
                    </li>
                @endrole

                @role('otdel-kadrov')
                <li class="nav-item">
                    <a href="{{ route('cabinet.employees.index') }}" class="nav-link  @if(request()->is('cabinet/employees*')) active @endif">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Список сотрудников
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cabinet.position.index') }}" class="nav-link @if(request()->is('cabinet/position*')) active @endif">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Список должностей</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cabinet.department.index') }}" class="nav-link @if(request()->is('cabinet/department*')) active @endif">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Подразделение</p>
                    </a>
                </li>
                @endrole

                @role('kpp-direktor')

                <li class="nav-item">
                    <a href="{{ route('cabinet.report.index') }}" class="nav-link @if(request()->is('cabinet/reports*')) active @endif">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Отчёты по машинам
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cabinet.white-car-list.index') }}" class="nav-link @if(request()->is('cabinet/white-car-list*')) active @endif">
                        <i class="nav-icon fas fa-car"></i>
                        <p>
                            Машины (белые)
                        </p>
                    </a>
                </li>
                @endrole

                @if(Auth::user()->company->ashana == 0)
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-copy"></i>
                        <p>
                            Асхана
                            <i class="fas fa-angle-left right"></i>
                            <span class="badge badge-info right">3</span>
                        </p>
                    </a>

                    <ul class="nav nav-treeview" style="margin-left: 15px; @if(request()->is('cabinet/wms/*')) display: block; @endif" >
                        <li class="nav-item">
                            <a href="{{ route('cabinet.ashana.index') }}" class="nav-link @if(request()->is('cabinet/ashana*')) active @endif">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>
                                    История
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('cabinet.ashana.talon') }}" class="nav-link @if(request()->is('cabinet/ashana/talon*')) active @endif">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>
                                    Мой талон
                                </p>
                            </a>
                        </li>

                        @role('ashana-otchety')
                        <li class="nav-item">
                            <a href="{{ route('cabinet.ashana.reports') }}" class="nav-link @if(request()->is('cabinet/ashana/reports*')) active @endif">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>
                                    Отчеты
                                </p>
                            </a>
                        </li>
                        @endrole

                    </ul>
                </li>
                @endif

                @role('wms-barcode-location')
                <li class="nav-item">
                    <a href="{{ route('cabinet.barcodeForWmsBoxes') }}" class="nav-link @if(request()->is('cabinet/barcode-for-wms-boxes*')) active @endif">
                        <i class="nav-icon fa fa-barcode"></i>
                        <p>
                            Barcode
                        </p>
                    </a>
                </li>
                @endrole

                @role('bosch-operator')
                <li class="nav-item">
                    <a href="{{ route('cabinet.barcode.bosch') }}" class="nav-link @if(request()->is('cabinet/bosch/barcode*')) active @endif">
                        <i class="nav-icon fa fa-barcode"></i>
                        <p>
                            Штрих-код
                        </p>
                    </a>
                </li>
                @endrole


                @if(Auth::user()->company->id == 2 || Auth::user()->company->id == 7)

                    @role('rezidenty-scango')
                    <li class="nav-item">
                        <a href="{{ route('cabinet.kpp.samsung') }}" class="nav-link @if(request()->is('cabinet/kpp*')) active @endif">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                SCANGO
                            </p>
                        </a>
                    </li>
                    @endrole

                    @role('samsung-operator')
                    <li class="nav-item">
                        <a href="{{ route('cabinet.barcode.samsung') }}" class="nav-link @if(request()->is('cabinet/samsung/barcode*')) active @endif">
                            <i class="nav-icon fa fa-barcode"></i>
                            <p>
                                Штрих-код
                            </p>
                        </a>
                    </li>
                    @endrole

                    <li class="nav-item">
                        <a href="{{ route('cabinet.dispatcher.index') }}" class="nav-link  @if(request()->is('cabinet/dispatcher*')) active @endif">
                            <i class="nav-icon fas fa-fax"></i>
                            <p>
                                Диспетчер
                            </p>
                        </a>
                    </li>

                    @role('rezidenty-webcont')
                    <li class="nav-item">
                        <a href="{{ route('cabinet.customs.index') }}" class="nav-link @if(request()->is('cabinet/customs*')) active @endif">
                            <i class="nav-icon fa fa-barcode"></i>
                            <p>
                                Приезд авто на ILC
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('cabinet.webcont.index') }}" class="nav-link @if(request()->is('cabinet/webcont*')) active @endif">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                WEBCONT
                            </p>
                        </a>
                    </li>
                    @endrole

                    @role('rezidenty-wms')
                    <li class="nav-item has-treeview @if(request()->is('cabinet/wms/*')) menu-open @endif">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-copy"></i>
                            <p>
                                WMS
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">5</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="margin-left: 15px; @if(request()->is('cabinet/wms/*')) display: block; @endif" >
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.orders') }}" class="nav-link @if(request()->is('cabinet/wms/orders*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Заказы</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.boxes') }}" class="nav-link @if(request()->is('cabinet/wms/boxes*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Ячейки</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.estore') }}" class="nav-link @if(request()->is('cabinet/wms/estore*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Хранение eStore</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.resend') }}" class="nav-link @if(request()->is('cabinet/wms/resend*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>EDI</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.palletSSCC') }}" class="nav-link @if(request()->is('cabinet/wms/pallet-sscc*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Bosch.Pallet.SSCC</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cabinet.wms.boschInvoices') }}" class="nav-link @if(request()->is('cabinet/wms/bosch/invoices*')) active @endif">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Bosch.INVOICES</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endrole
                @endif

                @role('rezidenty')

                <li class="nav-item">
                    <a href="{{ route('cabinet.employees.index') }}" class="nav-link  @if(request()->is('cabinet/employees*')) active @endif">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Список сотрудников
                        </p>
                    </a>
                </li>

                {{--<li class="nav-item">
                    <a href="{{ route('cabinet.position.index') }}" class="nav-link @if(request()->is('cabinet/position*')) active @endif">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Список должностей</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cabinet.department.index') }}" class="nav-link @if(request()->is('cabinet/department*')) active @endif">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Подразделение</p>
                    </a>
                </li>--}}

                <li class="nav-item">
                    <a href="{{ route('cabinet.white-car-list.index') }}" class="nav-link @if(request()->is('cabinet/white-car-list*')) active @endif">
                        <i style="font-size: 25px; vertical-align: super;" class="nav-icon fas fa-car"></i>
                        <p>
                            Постоянный пропуск <br> на автомашину
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cabinet.permits.index') }}" class="nav-link @if(request()->is('cabinet/permits*')) active @endif">
                        <i style="font-size: 25px; vertical-align: super;" class="nav-icon fas fa-align-justify"></i>
                        <p>
                            Разовый пропуск <br> на автомашину
                        </p>
                    </a>
                </li>

                @endrole

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
                @if(Auth::user()->company->type_company == 'technique')
                    <li class="nav-item">
                        <a href="{{ route('cabinet.technique.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                Список машин
                            </p>
                        </a>
                    </li>
                @endif

                @if(Auth::user()->company_id = 63)
                    <li class="nav-item">
                        <a href="{{ route('cabinet.webcont.aftos') }}" class="nav-link @if(request()->is('cabinet/webcont/aftos*')) active @endif">
                            <i class="nav-icon fas fa-align-justify"></i>
                            <p>
                                Поиск контейнера
                            </p>
                        </a>
                    </li>
                @endif

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
