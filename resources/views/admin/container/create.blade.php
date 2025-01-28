@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Добавить контейнер</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.containers.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Номер контейнера</label>
                    <input type="text" {{--onkeyup="this.value = this.value.replace(/\s+/g, '')"--}} class="form-control" minlength="11" maxlength="11" name="number" required>
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
