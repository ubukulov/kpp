@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Создать заявку на прием технику</h4>

                <technique-create-task :user="{{ json_encode(Auth::user()) }}" :technique_types="{{ json_encode($technique_types) }}"></technique-create-task>
            </div>
        </div>
    </div>
@stop
