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
            <h3 class="card-title">Редактировать подразделение</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('department.update', ['department' => $department->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Наименование</label>
                    <input type="text" value="{{ $department->title }}" class="form-control" name="title" required>
                </div>

                <div class="form-group">
                    <label>Компания</label>
                    <select name="company_id" class="form-control select2bs4">
                        @foreach($companies as $company)
                            <option @if($department->company_id == $company->id) selected  @endif value="{{ $company->id }}">{{ $company->short_en_name }}</option>
                        @endforeach
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
@push('admin_scripts')
    <!-- Select2 -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script>
        $(function () {
            //Initialize Select2 Elements
            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })
        });
    </script>
@endpush
