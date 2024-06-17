@extends('layouts.app')

@push('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

    <style>
        .select2-search__field {
            line-height: 20px;
            font-size: 20px !important;
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
                            <h4>Заявка №{{ $cargo_item->cargo->id }}</h4>
                        </div>
                    </div>
                    {{--<div class="col">
                        <div class="text-right">
                            <a class="btn btn-warning" href="{{ route('technique_parts.start', ['id' => 125]) }}">
                                <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>
                    </div>--}}

                    <div class="col-md-12">
                        <div class="positions">
                            <div class="card">
                                <div class="card-body" style="justify-content: space-around;">
                                    <h5>Позиция №{{ $cargo_item->id }}</h5>
                                    <p><strong>Наименование груза:</strong> {{ $cargo_item->cargo_tonnage->name }}</p>
                                    <p><strong>VINCODE:</strong> {{ $cargo_item->vincode }}</p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('cargo.controller.cargoItemStepTwoStore', ['cargoItem' => $cargo_item->id]) }}" method="post">
                            @csrf
                            <div class="card" style="margin-top: 4px;">
                                <div class="card-body">
                                    <div class="form-group" style="margin-left: 30px;">
                                        <h5>Выберите технику</h5>
                                        @foreach($techniques as $technique)
                                            <div class="form-check form-switch">
                                                <input style="font-size: 18px !important;" class="form-check-input" name="techniques[]" value="{{ $technique->id }}" type="checkbox" role="switch" id="flexSwitchCheckDefault{{ $technique->id }}">
                                                <label class="form-check-label" for="flexSwitchCheckDefault{{ $technique->id }}">{{ $technique->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div style="display: flex;">
                                        <div class="form-group">
                                            <label>Мест</label>
                                            <input type="number" required name="count_place" min="1" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Вес</label>
                                            <input type="number" required name="weight" step="0.0" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Кв.м</label>
                                            <input type="number" required name="square" step="0.0" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="card" style="margin-top: 4px;">
                                <div class="card-body">
                                    <div class="form-group">
                                        <h5>Выберите сотрудников</h5>
                                        <select name="employees[]" required multiple class="form-control js-example-basic-multiple">
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                            @endforeach
                                        </select>

                                        <br>

                                        <h5>Выберите площадки</h5>
                                        <select style="font-size: 20px !important;" name="cargo_area_id" required class="form-control">
                                            @foreach($cargo_areas as $cargoArea)
                                                <option value="{{ $cargoArea->id }}">{{ $cargoArea->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 10px; display: flex; justify-content: space-between;">
                                <a class="btn btn-outline-primary" href="{{ route('cargo.controller.cargoItems', ['id' => $cargo_item->cargo->id]) }}">
                                    <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                                </a>

                                <button style="font-size: 14px !important;" type="submit" class="btn btn-outline-primary">
                                    <i class="fa fa-play"></i>&nbsp;&nbsp;Сохранить и Продолжить
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <div class="col-md-12">

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@push('scripts')
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
