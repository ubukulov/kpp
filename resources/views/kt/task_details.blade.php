@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <a class="btn btn-primary" href="{{ route('kt.kt_operator') }}">Назад</a>
                <br><br>
                <table class="table table-bordered">
                    <thead>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Контейнер</th>
                    <th>Комменты</th>
                    <th>Статус</th>
                    <th>ИП</th>
                    <th>Дата</th>
                    </thead>
                    @foreach($import_logs as $log)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $log->user->full_name }}</td>
                            <td>
                                {{ $log->container_number }}
                            </td>
                            <td>
                                {{ $log->comments }}
                            </td>
                            <td>
                                @if($log->status == 'ok')
                                    <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>
                                @else
                                    <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                @endif
                            </td>
                            <td>
                                {{ $log->ip }}
                            </td>
                            <td>
                                {{ $log->import_date }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop
