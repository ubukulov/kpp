@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Добавить</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.container-address.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Наименование</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

                <div class="form-group">
                    <label>Тип</label>
                    <select name="kind" class="form-control">
                        <option value="r">Ряд</option>
                        <option value="k">Консоль</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ряд</label>
                    <input type="number" min="1" max="24" class="form-control" name="row" required>
                </div>

                <div class="form-group">
                    <label>Место</label>
                    <input type="number" min="1" max="9" class="form-control" name="place" required>
                </div>

                <div class="form-group">
                    <label>Ярус</label>
                    <input type="number" min="1" max="4" class="form-control" name="floor" required>
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
