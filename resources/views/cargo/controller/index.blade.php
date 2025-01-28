@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col">
                        <div class="text-left">
                            <h4>Список задач</h4>
                        </div>
                    </div>
                    <div class="col">

                    </div>

                    {{--<div class="col-md-12">
                        <div class="form-group">
                            <label style="font-size: 16px;">Фильтр:</label>
                            <select style="font-size: 20px !important;" class="form-control">
                                <option value="1">Новые Заявки</option>
                                <option value="2">В работе</option>
                                <option value="3">Закрытие Заявки</option>
                            </select>
                        </div>
                    </div>--}}

                    <div class="col-md-12">
                        @foreach($cargo as $item)
                            <a href="{{ route('cargo.controller.show', ['id' => $item->id]) }}">
                                <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-white rounded">
                                    <p>Заявка: №{{ $item->id }}</p>
                                    <p>Тип: {{ $item->getType() }}</p>
                                    <p>Клиент: {{ $item->company->full_company_name }}</p>
                                    <p>Статус: {{ __('words.cargo.'.$item->status) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
