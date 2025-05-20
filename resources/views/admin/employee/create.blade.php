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
            <h3 class="card-title">Добавить пользователя</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('employee.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Укажите компанию</label>
                            <select name="company_id" class="form-control select2bs4" style="width: 100%;">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Укажите подразделению</label>
                            <select name="department_id" class="form-control select2bs4" style="width: 100%;">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Укажите должность</label>
                            <select name="position_id" class="form-control select2bs4" style="width: 100%;">
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{--<div class="form-group">
                            <label>Укажите Группу (СКУД система)</label>
                            <select name="ckud_group_id" class="form-control select2bs4" style="width: 100%;">
                                <option value="0">Не нужно</option>
                                @foreach($ckud_groups->GetAcsEmployeeGroupsFull as $ckud_group)
                                    <option value="{{ $ckud_group->AcsEmployeeGroupId }}">{{ $ckud_group->name }}</option>
                                @endforeach
                            </select>
                        </div>--}}
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ФИО</label><span style="color: red;">*</span>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Телефон</label><span style="color: red;">*</span>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Пароль</label>
                                    <input type="text" class="form-control" name="password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <p>Укажите данные о трудоустройстве</p>

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Статус</label>
                            <select name="status" class="form-control">
                                <option value="works">Работает</option>
                                <option value="fired">Уволен</option>
                                <option value="on_holiday">В отпуске</option>
                                <option value="at_the_hospital">На больничном</option>
                                <option value="on_decree">На декрете</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>С</label>
                            <div class="input-group date" id="start_date" data-target-input="nearest">
                                <input type="text" name="start_date" class="form-control datetimepicker-input" data-target="#start_date"/>
                                <div class="input-group-append" data-target="#start_date" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ПО</label>
                            <div class="input-group date" id="end_date" data-target-input="nearest">
                                <input type="text" name="end_date" class="form-control datetimepicker-input" data-target="#end_date"/>
                                <div class="input-group-append" data-target="#end_date" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Описание</label>
                            <textarea name="description" cols="30" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <div style="background: #ccc; padding: 5px;">
                    <div class="row">
                        <div class="col-md-6">
                            <p>Укажите роли и разрешение к ним</p>
                            <div class="form-group">
                                <label>Выберите ролей</label>
                                <select name="roles[]" class="form-control" multiple>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Укажите разрешение</label>
                                <select name="permissions[]" class="form-control" multiple>
                                    @foreach($permissions as $permission)
                                        <option value="{{$permission->id}}">{{$permission->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <p>Для операторов КПП указать обязательно. Остальным пользователям не нужно</p>
                            <div class="form-group">
                                <label>Названия компьютера</label>
                                <input type="text" name="computer_name" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Названия принтера</label>
                                <input type="text" name="printer_name" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>КПП</label>
                                <select name="kpp_id" class="form-control">
                                    <option value="0">Не выбрано</option>
                                    @foreach($kpp as $item)
                                        <option value="{{$item->id}}">{{$item->title}}</option>
                                    @endforeach
                                </select>
                            </div>
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
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            $('#start_date, #end_date').datetimepicker({
                format: 'YYYY-MM-DD',
            });
        });
    </script>
@endpush
