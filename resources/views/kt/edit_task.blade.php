@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Изменить заявку</h4>

                <kt-operator-task-edit :container-task="{{json_encode($container_task)}}"></kt-operator-task-edit>
            </div>
        </div>
    </div>
@stop
