@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Изменить заявку</h4>

                <form action="{{ route('update.container-task', ['id' => $container_task->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Тип документа</label>
                        <select name="task_type" class="form-control">
                            <option @if($container_task->task_type == 'receive') selected @endif value="receive">Прием</option>
                            <option @if($container_task->task_type == 'ship') selected @endif value="ship">Выдача</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Тип транспорта</label>
                        <select name="trans_type" class="form-control">
                            <option @if($container_task->trans_type == 'train') selected @endif value="train">ЖД</option>
                            <option @if($container_task->trans_type == 'auto') selected @endif value="auto">Авто</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Документ основание (Скан)</label>
                        <input type="file" name="document_base" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Файл с переченем контейнеров (Эксель, xls)</label>
                        <input type="file" name="upload_file" required class="form-control">
                    </div>

                    <div class="form-group">
                        <button style="float: left;" type="button" onclick="window.location.href = '{{ route('kt.kt_operator') }}'" class="btn btn-primary">Назад</button>
                        <button style="float: right;" type="submit" class="btn btn-success">Обновить заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
