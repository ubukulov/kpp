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
            <h3 class="card-title">Добавить</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('admin.dispatcher.list.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col col-12">
                        <div class="form-group">
                            <label>Тип оповещание</label>
                            <select name="alert_id" class="form-control">
                                @foreach($alerts as $alert)
                                <option value="{{ $alert->id }}">{{ $alert->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <br>

                        <label>Способы оповещение</label> <br>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="sms" type="checkbox" id="inlineCheckbox1" value="sms">
                                    <label class="form-check-label" for="inlineCheckbox1">SMS</label>
                                </div>

                                <div class="form-group">
                                    <label>Сообщение</label>
                                    <textarea name="message_sms" class="form-control" cols="30" rows="4"></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="voice" type="checkbox" id="inlineCheckbox2" value="voice">
                                    <label class="form-check-label" for="inlineCheckbox2">Голосовое сообщение</label>
                                </div>

                                <div class="form-group">
                                    <label>Сообщение</label>
                                    <textarea name="message_voice" class="form-control" cols="30" rows="4"></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="whatsapp" type="checkbox" id="inlineCheckbox3" value="whatsapp">
                                    <label class="form-check-label" for="inlineCheckbox3">WhatsApp</label>
                                </div>

                                <div class="form-group">
                                    <label>Сообщение</label>
                                    <textarea name="message_whatsapp" class="form-control" cols="30" rows="4"></textarea>
                                </div>
                            </div>
                        </div>


                        <br>
                        <br>

                        <div class="form-group">
                            <label>Интервал в минутах</label>
                            <select name="interval" class="form-control">
                                @for($i=1; $i<20; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label>ФИО получателей</label>
                            <select name="users[]" required multiple class="form-control js-example-basic-multiple">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->short_ru_name }}, {{ $user->p_name }})</option>
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
