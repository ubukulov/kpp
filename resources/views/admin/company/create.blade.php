@extends('admin.admin')
@push('admin_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
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
                <div class="row">
                    <div class="col col-6">
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
                    </div>

                    <div class="col col-6">
                        <div class="form-group">
                            <label>БИН*</label>
                            <input type="text" class="form-control" name="bin" required>
                        </div>

                        <div class="form-group">
                            <label>Тип клиента</label>
                            <select name="type_company" class="form-control">
                                <option value="undefined">Не определено</option>
                                <option value="outsourcing">Аутсорсинг</option>
                                <option value="rent">Аренда</option>
                                <option value="resident">Резидент</option>
                                <option value="technique">Авто-техника</option>
                                <option value="cargo">Грузы</option>
                                <option value="damu_group">Damu Group</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Столовая. Есть договор?</label>
                            <select name="ashana" class="form-control">
                                <option value="0">Да</option>
                                <option value="1">Нет</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>КПП. </label>
                            <select name="kpp[]" required multiple class="form-control js-example-basic-multiple">
                                @foreach($kpp as $k)
                                    <option value="{{ $k->id }}">{{ $k->title }}</option>
                                @endforeach
                            </select>
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
@push('admin_scripts')
    <!-- Select2 -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script>
        $(function () {
            //Initialize Select2 Elements
            //Initialize Select2 Elements
            $('.js-example-basic-multiple').select2({
                theme: 'bootstrap4',
                tags: "true",
                placeholder: "Выберите",
                allowClear: true,
                closeOnSelect: false,
            });
        });
    </script>
@endpush
