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
            <h3 class="card-title">Редактировать пользователя</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('employee.update', ['employee' => $employee->id]) }}" method="POST" role="form">
            @csrf
            @method('PUT')
            <div class="card-body">

                <div class="form-group">
                    <label>Укажите компанию</label>
                    <select name="company_id" class="form-control select2bs4" style="width: 100%;">
                        @foreach($companies as $company)
                            <option @if($company->id == $employee->company_id) selected @endif value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Укажите должность</label>
                    <select name="position_id" class="form-control select2bs4" style="width: 100%;">
                        @foreach($positions as $position)
                            <option @if($position->id == $employee->position_id) selected @endif value="{{ $position->id }}">{{ $position->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>ФИО</label>
                    <input type="text" value="{{ $employee->full_name }}" class="form-control" name="full_name" required>
                </div>

                <div class="form-group">
                    <label>Телефон</label>
                    <input type="text" value="{{ $employee->phone }}" class="form-control" name="phone">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="{{ $employee->email }}" class="form-control" name="email">
                </div>

                <hr>

                <div style="background: #ccc; padding: 5px;">
                    <p>Укажите роли и разрешение к ним</p>
                    <div class="form-group">
                        <label>Выберите ролей</label>
                        <select name="roles[]" class="form-control" multiple>
                            @foreach($roles as $role)
                                <option @if($employee->hasRole($role->slug)) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Укажите разрешение</label>
                        <select name="permissions[]" class="form-control" multiple>
                            @foreach($permissions as $permission)
                                <option @if($employee->hasPermission($permission->slug)) selected @endif value="{{$permission->id}}">{{$permission->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <p>Для операторов КПП указать обязательно. Остальным пользователям не нужно</p>
                    <div class="form-group">
                        <label>Названия компьютера</label>
                        <input type="text" value="{{ $employee->computer_name }}" name="computer_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Названия принтера</label>
                        <input type="text" value="{{ $employee->printer_name }}" name="printer_name" class="form-control">
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
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })
        });
    </script>
@endpush
