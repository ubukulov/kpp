@extends('layouts.app')
@push('styles')
    <style>
        .kt{
            width: 900px;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col-md-3">
                        <a class="btn btn-success" href="{{ route('o.create.task') }}">Создать заявку</a>
                    </div>

                    <div class="col-md-6">
                        <p>Пользователь: <strong>{{ Auth::user()->full_name }}</strong></p>
                    </div>

                    <div class="col-md-3 text-right">
                        <a class="btn btn-primary" href="{{ route('logout') }}">Выйти из аккаунта</a>
                    </div>
                </div>
                <br><br>
                <table class="table table-bordered">
                    <thead>
                        <th>Заявка</th>
                        <th>Тип</th>
                        <th>Тип авто</th>
                        <th>Статус</th>
                        <th>Файл</th>
                        <th>История</th>
                        <th>Ред.</th>
                        <th>Дата</th>
                    </thead>
                    @foreach($container_tasks as $task)
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td>
                            @if($task->task_type == 'receive')
                                Прием
                            @else
                                Выдача
                            @endif
                        </td>
                        <td>
                            @if($task->trans_type == 'train')
                                ЖД
                            @else
                                Авто
                            @endif
                        </td>
                        <td>
                            @if($task->status == 'open')
                                В работе <a href="{{ route('show.task-container-logs', ['id' => $task->id]) }}"><i style="font-size: 20px;" class="fa fa-history"></i></a>
                            @elseif($task->status == 'failed')
                                Ошибка при импорте
                            @else
                                Выполнен
                            @endif
                        </td>
                        <td>
                            <a href="{{ $task->upload_file }}" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Скачать</a>
                        </td>
                        <td>
                            <a href="{{ route('show.task-import-logs', ['id' => $task->id]) }}"><i style="font-size: 20px;" class="fa fa-history"></i></a>
                        </td>
                        <td>
                            @if($task->status == 'failed')
                            <a href="{{ route('edit.task', ['id' => $task->id]) }}"><i style="font-size: 20px;" class="fa fa-edit"></i></a>
                            @endif
                        </td>
                        <td>
                            {{ $task->created_at }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop
