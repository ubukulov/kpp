@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Добавить компанию</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('company.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Полное наименование компании</label>
                    <input type="text" class="form-control" name="full_company_name" required>
                </div>

                <div class="form-group">
                    <label>Короткое название на русском</label>
                    <input type="text" class="form-control" name="short_ru_name" required>
                </div>

                <div class="form-group">
                    <label>Короткое название на английском</label>
                    <input type="text" class="form-control" name="short_en_name" required>
                </div>

                <div class="form-group">
                    <label>Адрес на территории</label>
                    <input type="text" class="form-control" name="address" required>
                </div>

                <div class="form-group">
                    <label>Вид деятельности</label>
                    <input type="text" class="form-control" name="kind_of_activity" required>
                </div>

                <div class="form-group">
                    <label>Тип клиента</label>
                    <select name="type_company" class="form-control">
                        <option value="undefined">Не определено</option>
                        <option value="outsourcing">Аутсорсинг</option>
                        <option value="rent">Аренда</option>
                        <option value="resident">Резидент</option>
                    </select>
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
