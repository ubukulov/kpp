<div style="background: #ccc; padding: 5px;">
    <div class="row">
        <div class="col-md-6">
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
        </div>
        <div class="col-md-6">
            <p>Для операторов КПП указать обязательно. Остальным пользователям не нужно</p>
            <div class="form-group">
                <label>Названия компьютера</label>
                <input type="text" value="{{ $employee->computer_name }}" name="computer_name" class="form-control">
            </div>

            <div class="form-group">
                <label>Названия принтера</label>
                <input type="text" value="{{ $employee->printer_name }}" name="printer_name" class="form-control">
            </div>

            <div class="form-group">
                <label>КПП</label>
                <select name="kpp_id" class="form-control">
                    <option value="0">Не выбрано</option>
                    @foreach($kpp as $item)
                        <option @if($employee->kpp_name == $item->name) selected @endif value="{{$item->id}}">{{$item->title}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div style="background: #d3d9df; padding: 5px;margin-top: 20px;">
    <p>Для роли "Отдел кадров" указываем какие компании и подразделение курирует.</p>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Компании </label>
                <select name="companies[]" multiple class="form-control js-example-basic-multiple">
                    @foreach($companies as $company)
                        <option @if($employee->hasItemInSettings('human_resources_departments', 'companies', $company->id)) selected @endif value="{{ $company->id }}">{{ $company->short_en_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php

        @endphp

        <div class="col-md-6">
            <div class="form-group">
                <label>Подразделение </label>
                <select name="departments[]" multiple class="form-control js-example-basic-multiple">
                    @foreach($departments as $department)
                        <option @if($employee->hasItemInSettings('human_resources_departments', 'departments', $department->id)) selected @endif value="{{ $department->id }}">{{ $department->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
