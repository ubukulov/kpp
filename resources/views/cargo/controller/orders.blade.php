@extends('layouts.app')
@section('content')
    <div class="row" data-app>
        <div class="col-md-12" style="padding: 0px;">
            @include('partials.kt_header')
        </div>

        <cargo-controller :user="{{ json_encode(Auth::user()) }}"></cargo-controller>
    </div>
@stop
