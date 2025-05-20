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
                    <th>#</th>
                    <th>VIN</th>
                    <th>Владелец</th>
                    <th>Марка</th>
                    <th>Тип</th>
                    <th>Место</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    </thead>
                    @foreach($stocks as $stock)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $stock->vin_code }}
                            </td>
                            <td>
                                @if($technique_task->status == 'closed' && $technique_task->task_type == 'ship')
                                    {{ $stock->owner }}
                                @else
                                    {{ $stock->short_en_name }}
                                @endif
                            </td>
                            <td>
                                {{ $stock->mark }}
                            </td>
                            <td>
                                {{ $stock->technique_type }}
                            </td>
                            <td>
                                @if(is_null($stock->technique_place_id))
                                    Не определено
                                @else
                                    {{ $stock->technique_place->name }}
                                @endif
                            </td>
                            <td>
                                @if($stock->status == 'incoming')
                                    Не размещено
                                @elseif($stock->status == 'received')
                                    Размещено
                                @elseif($stock->status == 'in_order')
                                    Необходимо выдать
                                @elseif($stock->status == 'exit_pass')
                                    Корешок подписан
                                @elseif($stock->status == 'canceled')
                                    Отменен
                                @else
                                    Выдано
                                @endif
                            </td>
                            <td>
                                {{ $stock->updated_at }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop
