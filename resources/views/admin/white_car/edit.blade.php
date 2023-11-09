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
            <h3 class="card-title">Редактировать</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.white-car-list.update', ['white_car_list' => $white_car_list->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Наименование</label>
                    <input type="text" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" class="form-control" value="{{ $white_car_list->gov_number }}" name="gov_number" required>
                </div>

                <div class="form-group">
                    <label>Клиент</label>
                    <select name="company_id" class="form-control select2bs4">
                        @foreach($companies as $company)
                            <option @if($company->id == $wcl_company->company_id) selected @endif value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Статус</label>
                    <select name="status" class="form-control">
                        <option @if($wcl_company->status == 'ok') selected  @endif value="ok">Доступ разрешен</option>
                        <option @if($wcl_company->status == 'not') selected  @endif value="not">Доступ запрещен</option>
                    </select>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a style="margin-left: 20px;" href="{{ route('admin.white-car-list.index') }}" class="btn btn-warning">Отменить</a>
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
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush
