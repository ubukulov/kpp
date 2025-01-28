@extends('layouts.app')
@push('styles')
    <style>
        .card{
            margin-bottom: 10px;
            font-family: monospace;
            border: 1px dashed;
        }
    </style>
@endpush
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
                            <a class="btn btn-warning" href="{{ route('cargo.controller.show', ['id' => $cargo->id]) }}">
                                <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-white rounded">
                            @foreach($cargo->cargo_items as $cargoItem)
                                <div class="positions">
                                    <div class="card">
                                        <div class="card-body" style="justify-content: space-around;">
                                            <h5>Позиция №{{ $loop->iteration }}</h5>
                                            <div class="form-group">
                                                <label>Вид груза</label>
                                                <select disabled class="form-control">
                                                    @foreach($cargo_tonnage as $cargoTonnage)
                                                        <option @if($cargoTonnage->id == $cargoItem->cargo_tonnage_type_id) selected @endif value="{{ $cargoTonnage->id }}">{{ $cargoTonnage->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Номер машины</label>
                                                <input type="text" disabled value="{{ $cargoItem->car_number }}" class="form-control">
                                            </div>

                                            <div class="form-group">
                                                <label>Статус</label>
                                                <p>{{ __('words.cargo.'.$cargoItem->status) }}</p>
                                            </div>

                                            @if($cargoItem->status == 'waiting' || $cargoItem->status == 'processing')

                                            <div class="form-group">
                                                <label>Действие</label>
                                                <a style="font-size: 20px; line-height: 30px;" href="{{ route('cargo.controller.cargoItemStepTwo', ['cargoItem' => $cargoItem->id]) }}" class="btn btn-outline-primary form-control">Начать</a>
                                            </div>

                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
