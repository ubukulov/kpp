@extends('cabinet.cabinet')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Доступные модули</h3>
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <div class="row mt-2 mb-2">
                        @foreach(Auth::user()->roles as $role)
                                @if($role->slug == 'kt-controller')
                                <div class="col-12 mb-4">
                                    <a href="{{ route('cabinet.webcont.kt.controller') }}" class="user_module">
                                        Интерфейс: Контейнер контроллер
                                    </a>
                                </div>
                                @endif

                                @if($role->slug == 'technique-controller')
                                    <div class="col-12 mb-4">
                                        <a href="{{ route('cabinet.webcont.technique.controller') }}" class="user_module">
                                            Интерфейс: Авто контроллер
                                        </a>
                                    </div>
                                @endif

                                @if($role->slug == 'cargo-controller')
                                    <div class="col-12 mb-4">
                                        <a href="{{ route('cabinet.webcont.cargo.controller') }}" class="user_module">
                                            Интерфейс: Груз контроллер
                                        </a>
                                    </div>
                                @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('cabinet_styles')
    <style>
        .user_module {
            padding: 20px;
            background: steelblue;
            color: #fff;
            border-radius: 10px;
            display: block;
        }
    </style>
@endpush
