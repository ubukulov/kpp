@extends('layouts.app')
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
                        @if($container_task->status == 'open')
                        <button @if(!$container_task->allowCloseThisTask()) disabled="disabled" title="Не все позиции выполнены"  @endif style="font-size: 14px !important;" class="btn btn-success" onclick="window.location.href = '{{ route('completed.task', ['id' => $container_task->id]) }}'">Закрыть заявку</button>
                        @endif
                    </div>
                </div>

                <br><br>
                <table class="table table-bordered">
                    <thead>
                    <th>#</th>
                    <th>Контейнер</th>
                    <th>Статус</th>
                    <th>Адрес</th>
                    <th>Дата</th>
                    </thead>
                    @foreach($import_logs as $im)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $im->container_number }}</td>
                            <td>
                                @if($container_task->task_type == 'receive')
                                    @if($im->state == 'posted')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.posted') }}
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_posted') }}
                                    @endif
                                @endif

                                @if($container_task->task_type == 'ship')
                                    @if($im->state == 'selected')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.selected') }}
                                    @elseif($im->state == 'issued')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.issued') }}
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_selected') }}
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $im->getContainerAddress() }}
                            </td>
                            <td>
                                {{ $im->updated_at->format('d.m.Y H:i:s') }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop
