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
                    <div class="col-md-6">
                        <a class="btn btn-primary" href="{{ route('kt.kt_operator') }}">Назад</a>
                    </div>

                    <div class="col-md-6 text-right">
                        <button @if(!$container_task->allowCloseThisTask($container_stocks)) disabled="disabled" title="Не все позиции выполнены"  @endif style="font-size: 14px !important;" class="btn btn-success" onclick="window.location.href = '{{ route('completed.task', ['id' => $container_task->id]) }}'">Закрыть заявку</button>
                    </div>
                </div>

                <br><br>
                <table class="table table-bordered">
                    <thead>
                    <th>#</th>
                    <th>Контейнер</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    </thead>
                    @foreach($container_stocks as $container_stock)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $container_stock->container->number }}</td>
                            <td>
                                @if($container_task->task_type == 'receive')
                                    @if($container_stock->status == 'received')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; Размещен
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; Не размещен
                                    @endif
                                @endif

                                @if($container_task->task_type == 'ship')
                                    @if($container_stock->status == 'shipped')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; Отобран
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; Не отобран
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $container_stock->updated_at }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop
