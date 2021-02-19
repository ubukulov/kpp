@extends('admin.admin')
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Редактировать компанию</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('company.update', ['company' => $company->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Полное наименование компании</label>
                    <input type="text" class="form-control" value="{{ $company->full_company_name }}" name="full_company_name" required>
                </div>

                <div class="form-group">
                    <label>Короткое название на русском</label>
                    <input type="text" class="form-control" value="{{ $company->short_ru_name }}" name="short_ru_name" required>
                </div>

                <div class="form-group">
                    <label>Короткое название на английском</label>
                    <input type="text" class="form-control" value="{{ $company->short_en_name }}" name="short_en_name" required>
                </div>

                <div class="form-group">
                    <label>Адрес на территории</label>
                    <input type="text" class="form-control" value="{{ $company->address }}" name="address" required>
                </div>

                <div class="form-group">
                    <label>Вид деятельности</label>
                    <input type="text" class="form-control" value="{{ $company->kind_of_activity }}" name="kind_of_activity" required>
                </div>

                <div class="form-group">
                    <label>Тип клиента</label>
                    <select name="type_company" class="form-control">
                        <option @if($company->type_company == 'undefined') selected @endif value="undefined">Не определено</option>
                        <option @if($company->type_company == 'outsourcing') selected @endif value="outsourcing">Аутсорсинг</option>
                        <option @if($company->type_company == 'rent') selected @endif value="rent">Аренда</option>
                        <option @if($company->type_company == 'resident') selected @endif value="resident">Резидент</option>
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
