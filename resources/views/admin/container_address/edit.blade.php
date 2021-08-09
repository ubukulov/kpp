@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Редактировать должность</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.container-address.update', ['container_address' => $container_address->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Наименование</label>
                    <input type="text" value="{{ $container_address->title }}" class="form-control" name="title" required>
                </div>

                <div class="form-group">
                    <label>Тип</label>
                    <select name="kind" class="form-control">
                        <option @if($container_address->kind == 'r') selected @endif value="r">Ряд</option>
                        <option @if($container_address->kind == 'k') selected @endif value="c">Консоль</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ряд</label>
                    <input type="number" value="{{ $container_address->row }}" min="1" max="24" class="form-control" name="row" required>
                </div>

                <div class="form-group">
                    <label>Место</label>
                    <input type="number" value="{{ $container_address->place }}" min="1" max="9" class="form-control" name="place" required>
                </div>

                <div class="form-group">
                    <label>Ярус</label>
                    <input type="number" value="{{ $container_address->floor }}" min="1" max="4" class="form-control" name="floor" required>
                </div>

                <div class="form-group">
                    <label>Площадка</label>
                    <input type="text" value="{{ $container_address->space }}" class="form-control" name="space" required>
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
