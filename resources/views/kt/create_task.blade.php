@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Создать заявку на прием контейнеров</h4>

                <kt-operator-task-create :user="{{ json_encode(Auth::user()) }}"></kt-operator-task-create>
            </div>
        </div>
    </div>
@stop
