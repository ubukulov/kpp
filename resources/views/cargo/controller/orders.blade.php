@extends('cabinet.cabinet-vue')
@section('content')
    <div class="row" data-app>
        <div class="col-md-12">
            <img src="/img/cont.png" alt="container" style="width: 100%;">
        </div>

        <cargo-controller :user="{{ json_encode(Auth::user()) }}"></cargo-controller>
    </div>
@stop
