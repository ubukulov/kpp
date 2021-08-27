@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Редактировать пропуск №{{ $permit->id }}</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.permits.update', ['permit' => $permit->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Вх.контейнер</label>
                            <input type="text" min="11" max="11" maxlength="11" name="incoming_container_number" value="{{ $permit->incoming_container_number }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Исх.контейнер</label>
                            <input type="text" min="11" max="11" maxlength="11" name="outgoing_container_number" value="{{ $permit->outgoing_container_number }}" class="form-control">
                        </div>
                    </div>
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
