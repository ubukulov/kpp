@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Редактировать контейнер</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.containers.update', ['container' => $container->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Номер контейнера</label>
                    <input type="text" class="form-control" minlength="11" maxlength="11" name="number" value="{{ $container->number }}" required>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
