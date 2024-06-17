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

                    <div class="col-md-12">

                        <div class="positions">
                            <div class="card">
                                <div class="card-body" style="justify-content: space-around;">
                                    <h5>Позиция №2</h5>
                                    <p><strong>Наименование груза:</strong> {{ $cargo_item->cargo_tonnage->name }}</p>
                                    <p><strong>VINCODE:</strong> {{ $cargo_item->vincode }}</p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('cargo.controller.cargoItemStepThreeStore', ['cargoItem' => $cargo_item->id]) }}" method="post">
                            @csrf
                            <div class="card" style="margin-top: 4px;">
                                <div class="card-body">
                                    <div class="form-group">
                                        <h5>Вид работы</h5>
                                        <select name="cargo_work_types[]" required multiple class="form-control js-example-basic-multiple">
                                            @foreach($cargo_work_types as $cargoWorkType)
                                                <option value="{{ $cargoWorkType->id }}">{{ $cargoWorkType->name }}</option>
                                            @endforeach
                                        </select>

                                        <br>
                                        <h5>Вид</h5>
                                        <select style="font-size: 20px !important;" name="type" required class="form-control">
                                            <option value="manual">Ручная</option>
                                            <option value="automatic">Механизированная</option>
                                        </select>

                                        <br>
                                        {{--<h5>Дополнительно</h5>
                                        <select name="emps[]" required multiple class="form-control js-example-basic-multiple">
                                            <option value="1">Уборка снега</option>
                                            <option value="2">Уборка территории</option>
                                            <option value="3">Погрузка мусора</option>
                                        </select>--}}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 10px; display: flex; justify-content: space-between;">
                                <a class="btn btn-outline-primary" href="{{ route('cargo.controller.cargoItemStepTwo', ['cargoItem' => $cargo_item->id]) }}">
                                    <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                                </a>

                                <button style="font-size: 14px !important;" type="submit" class="btn btn-outline-primary">
                                    <i class="fa fa-play"></i>&nbsp;&nbsp;Сохранить и Завершить
                                </button>
                            </div>
                        </form>

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
