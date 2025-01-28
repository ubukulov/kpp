@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col">
                        <div class="text-left">
                            <h4>Заявка №{{ $id }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-right">
                            <a class="btn btn-warning" href="{{ route('cargo.index') }}">
                                <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        @if($id == 124)
                            <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-pink rounded">
                                <p><strong>Заявка:</strong> №124</p>
                                <p><strong>Тип:</strong> Погрузка</p>
                                <p><strong>Клиент:</strong> ТОО FIRST MOTOR GROUP</p>
                                <p><strong>Техники:</strong> Автокран, Автокара</p>
                                <p><strong>Сотрудники:</strong> Абаев Т, Тимуров С</p>
                                <p><strong>Площадка:</strong> Площадка1</p>
                                <p><strong>Вид работы:</strong> Выгрузка груза, предоставление пандуса</p>
                                <p><strong>Вид выгрузки/погрузки:</strong> Механизированная</p>
                                <p><strong>Количество мест:</strong> 9</p>
                                <p><strong>Вес:</strong> 5т</p>
                                <p><strong>Площадь, кв.м:</strong> 9</p>
                                <p><strong>Дополнительно:</strong> нет</p>
                                <p><strong>Дата начало:</strong> 20.03.2024 10:20</p>
                                <p><strong>Дата конец:</strong>  </p>
                                <p><strong>Статус:</strong> В работе</p>
                            </div>
                        @elseif($id == 125)
                            <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-white rounded">
                                <p><strong>Заявка:</strong> №125</p>
                                <p><strong>Тип:</strong> Выгрузка</p>
                                <p><strong>Клиент:</strong> ТОО FIRST MOTOR GROUP</p>
                                <p><strong>Количество позиции:</strong> 3</p>
                                <p><strong>Статус:</strong> Новый</p>

                                <br>

                                <a href="{{ route('cargo.start', ['id' => 125]) }}" class="btn btn-success">
                                    <i class="fa fa-play"></i>&nbsp;&nbsp;Начать работу
                                </a>
                            </div>
                        @else
                            <div style="color: #000;" class="shadow-sm p-3 mb-5 bg-white rounded">
                                <p><strong>Заявка:</strong> №123</p>
                                <p><strong>Тип:</strong> Выгрузка</p>
                                <p><strong>Клиент:</strong> ТОО FIRST MOTOR GROUP</p>
                                <p><strong>Техники:</strong> Автокран, Автокара</p>
                                <p><strong>Сотрудники:</strong> Абаев Т, Тимуров С</p>
                                <p><strong>Площадка:</strong> Площадка1</p>
                                <p><strong>Вид работы:</strong> Выгрузка груза, предоставление пандуса</p>
                                <p><strong>Вид выгрузки/погрузки:</strong> Механизированная</p>
                                <p><strong>Количество мест:</strong> 9</p>
                                <p><strong>Вес:</strong> 5т</p>
                                <p><strong>Площадь, кв.м:</strong> 9</p>
                                <p><strong>Дополнительно:</strong> нет</p>
                                <p><strong>Дата начало:</strong> 15.03.2024 09:30</p>
                                <p><strong>Дата конец:</strong>  15.03.2024 15:45</p>
                                <p><strong>Статус:</strong> Выполнен</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
