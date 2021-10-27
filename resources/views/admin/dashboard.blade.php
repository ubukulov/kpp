@extends('admin.admin')
@section('content')
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\ContainerStock::all()->count() }}</h3>

                    <p>Контейнеров на площадке</p>
                </div>
                <div class="icon">
                    <img style="position: absolute;right: 15px;top: 15px;transition: all .3s linear;opacity: 0.2;" width="100" src="/img/container.png" alt="">
                </div>
                <a href="{{ route('admin.webcont.stocks') }}" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Http\Controllers\Admin\StatController::getOperationCraneOperatorForToday() }}</h3>

                    <p>Операций за текущий день</p>
                </div>
                <div class="icon">
                    <img style="position: absolute;right: 15px;top: 15px;transition: all .3s linear;opacity: 0.2;" width="100" src="/img/crane.png" alt="">
                </div>
                <a href="{{ route('admin.webcont.logs') }}" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\User::all()->count() }}</h3>

                    <p>Пользователей</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ route('employee.index') }}" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ \App\Models\Permit::getCountPermitsForToday() }}</h3>

                    <p>выданных пропусков за сегодня</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="{{ route('admin.permits.index') }}" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->
@stop
