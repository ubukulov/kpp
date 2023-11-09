<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Укажите компанию</label>
            <select name="company_id" class="form-control select2bs4" style="width: 100%;">
                @foreach($companies as $company)
                    <option @if($company->id == $employee->company_id) selected @endif value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Укажите подразделению</label>
            <select name="department_id" class="form-control select2bs4" style="width: 100%;">
                @foreach($departments as $department)
                    <option @if($department->id == $employee->department_id) selected @endif value="{{ $department->id }}">{{ $department->title }}</option>
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

        <div class="form-check">
            <input class="form-check-input" @if($employee->badge == 1) checked @endif name="badge" type="checkbox" id="hasBadge">
            <label class="form-check-label" for="hasBadge">
                Выдал бейджик ?
            </label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>ФИО</label>
            <input type="text" value="{{ $employee->full_name }}" class="form-control" name="full_name" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>ИИН</label><span style="color: red;">*</span>
                    <input type="text" value="{{ $employee->iin }}" required class="form-control" name="iin">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Телефон</label><span style="color: red;">*</span>
                    <input type="text" value="{{ $employee->phone }}" class="form-control" name="phone">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="{{ $employee->email }}" class="form-control" name="email">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="text" placeholder="Если поле оставить пустой, то пароль не изменяться!" class="form-control" name="password">
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<p>Укажите данные о трудоустройстве</p>

<div class="row">
    <div class="col-md-2">
        @php
            if($employee->getWorkingStatus()) {
                $working_status = $employee->getWorkingStatus();
            } else {
                $working_status = null;
            }

        @endphp
        <div class="form-group">
            <label>Статус</label>
            <select name="status" class="form-control">
                <option @if($working_status && $working_status->status == 'works') selected @endif value="works">Работает</option>
                <option @if($working_status && $working_status->status == 'fired') selected @endif value="fired">Уволен</option>
                <option @if($working_status && $working_status->status == 'on_holiday') selected @endif value="on_holiday">В отпуске</option>
                <option @if($working_status && $working_status->status == 'at_the_hospital') selected @endif value="at_the_hospital">На больничном</option>
                <option @if($working_status && $working_status->status == 'on_decree') selected @endif value="on_decree">На декрете</option>
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>С</label>
            <div class="input-group date" id="start_date" data-target-input="nearest">
                <input type="text" @if($working_status) value="{{ $working_status->start_date }}" @endif name="start_date" class="form-control datetimepicker-input" data-target="#start_date"/>
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
                <input type="text" @if($working_status) value="{{ $working_status->end_date }}" @endif name="end_date" class="form-control datetimepicker-input" data-target="#end_date"/>
                <div class="input-group-append" data-target="#end_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Описание</label>
            <textarea name="description" cols="30" rows="4" class="form-control">@if($working_status) {{ $working_status->description }} @endif</textarea>
        </div>
    </div>
</div>
