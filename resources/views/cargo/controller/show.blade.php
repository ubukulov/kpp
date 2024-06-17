@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col">
                        <div class="text-left">
                            <h4>Заявка №{{ $cargo->id }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-right">
                            <a class="btn btn-warning" href="{{ route('cargo.controller') }}">
                                <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-white rounded">
                            <p><strong>Тип:</strong> {{ $cargo->getType() }}</p>
                            <p><strong>Клиент:</strong> {{ $cargo->company->full_company_name }}</p>
                            <p><strong>Количество позиции:</strong> {{ count($cargo->cargo_items) }}</p>
                            <p><strong>Статус:</strong> {{ __('words.cargo.'.$cargo->status) }}</p>

                            <br>

                            @if($cargo->status == 'new' || $cargo->status == 'processing')

                                <a href="{{ route('cargo.controller.cargoItems', ['id' => $cargo->id]) }}" class="btn btn-success">
                                    <i class="fa fa-play"></i>&nbsp;&nbsp;Начать работу
                                </a>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
