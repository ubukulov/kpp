@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                {{--<div class="row">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('o.create.task') }}">Создать заявку</a>
                    </div>

                    <div class="col-md-6 text-right">
                        <a class="btn btn-primary" href="{{ route('logout') }}">Выйти из аккаунта</a>
                    </div>
                </div>--}}

                <v-app>
                    <v-container>
                        <div style="padding: 10px; text-align: center;">
                            <kt :user="{{ json_encode(Auth::user()) }}"></kt>
                        </div>
                    </v-container>
                </v-app>
            </div>
        </div>
    </div>
@stop

