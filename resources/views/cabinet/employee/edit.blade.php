@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Редактировать данные сотрудника</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('cabinet.employees.update', ['employee' => $employee->id]) }}" method="POST" role="form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Компания</label>
                            @if(Auth::user()->hasRole('otdel-kadrov'))
                                <select name="company_id" class="form-control select2bs4" style="width: 100%;">
                                    @foreach($companies as $company)
                                        <option @if($company->id == $employee->company_id) selected @endif value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select disabled name="company_id" class="form-control select2bs4" style="width: 100%;">
                                    @foreach($companies as $company)
                                        <option @if($company->id == $employee->company_id) selected @endif value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Подразделение</label>
                            <select name="department_id" class="form-control select2bs4" style="width: 100%;">
                                @foreach($departments as $department)
                                    <option @if($department->id == $employee->department_id) selected @endif value="{{ $department->id }}">{{ $department->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Должность</label>
                            <select name="position_id" class="form-control select2bs4" style="width: 100%;">
                                @foreach($positions as $position)
                                    <option @if($position->id == $employee->position_id) selected @endif value="{{ $position->id }}">{{ $position->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">

                        <div class="form-group">
                            <label>ФИО</label><span style="color: red;">*</span>
                            <input type="text" value="{{ $employee->full_name }}" class="form-control" name="full_name" required>
                        </div>

                        <div class="form-group">
                            <label>Телефон</label><span style="color: red;">*</span>
                            <input type="text" required value="{{ $employee->phone }}" class="form-control" name="phone">
                        </div>
                    </div>
                </div>

                <hr>

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
                            <label>Текущий статус</label>
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
                            <label>Дополнительная информация</label>
                            <textarea name="description" cols="30" rows="4" class="form-control">@if($working_status) {{ $working_status->description }} @endif</textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Выбрать файл</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Веб-камера</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row">
                            <div class="col-md-12">
                                @if(!empty($employee->image))
                                    <div id="camera2" style="max-width: 100%; margin-bottom: 10px;">
                                        <img width="640" height="480" style="max-width: 100%;" src="{{ $employee->image }}"/>
                                    </div>
                                @endif

                                <input type="file" class="form-control-file" name="change_avatar">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-size: 14px;">Фото*</label>
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <div id="camera" style="max-width: 100%; margin-bottom: 10px;"></div>
                                            <div style="margin-left: 200px;">
                                                <button style="margin-right: 20px;" id="image-fire" type=button class="btn btn-warning">Сделать фото</button>
                                                <button style="margin-right: 20px;" id="image-reset" type=button class="btn btn-dark">Сброс</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="path_docs_fac" name="path_docs_fac" class="image-tag">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--<div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label style="font-size: 14px;">Фото*</label>
                            <div class="row justify-content-center">
                                <div class="col-md-12 text-center">
                                    <div id="camera" style="max-width: 100%; margin-bottom: 10px;"></div>
                                    <div>
                                        <button style="margin-right: 20px;" id="image-fire" type=button class="btn btn-warning">Снимать</button>
                                        <button style="margin-right: 20px;" id="image-reset" type=button class="btn btn-dark">Сброс</button>
                                    </div>
                                </div>
                                <input type="hidden" id="path_docs_fac" name="path_docs_fac" class="image-tag">
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        @if(!empty($employee->image))
                        <div id="camera2" style="max-width: 100%; margin-bottom: 10px;">
                            <img style="max-width: 100%;" src="{{ $employee->image }}"/>
                        </div>
                        @endif

                        <input type="file" class="form-control-file" name="change_avatar">
                    </div>
            </div>--}}
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
@push('cabinet_scripts')
    <!-- Select2 -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script src="/js/webcam.js"></script>
    <script>
        Webcam.set({
            width: 640,
            height: 480,
            image_format: 'jpeg',
            jpeg_quality: 90
        });
        Webcam.attach('#camera');
    </script>
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

            $('#image-fire').click(function (e) {
                e.preventDefault();
                Webcam.snap( function(data_uri) {
                    $("#path_docs_fac").val(data_uri);
                    document.getElementById('camera').innerHTML = '<img style="max-width: 100%;" src="'+data_uri+'"/>';
                });
            });
            $('#image-reset').click(function(e){
                e.preventDefault();
                document.getElementById('camera').innerHTML = '';
                Webcam.set({
                    width: 640,
                    height: 480,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach('#camera');
            });
        });
    </script>
@endpush
