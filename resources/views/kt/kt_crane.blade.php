@extends('layouts.app')
@push('styles')
    <style>
        .kt{
            width: 900px;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
@endpush
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
                            <kt></kt>
                        </div>
                    </v-container>
                </v-app>
            </div>
        </div>
    </div>
@stop

