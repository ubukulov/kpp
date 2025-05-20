@extends('layouts.app')
@section('content')
    <div class="row" data-app>
        <div class="col-md-12" style="padding: 0px;">
            @include('partials.kt_header')
        </div>

        <cargo-order-position
            :cargo-task="{{ json_encode($cargoTask) }}"
            :cargo-stock="{{ json_encode($cargoStock) }}"
            :cargo-log="{{ json_encode($cargoLog) }}"
            :user="{{ json_encode(Auth::user())  }}"
        ></cargo-order-position>
    </div>
@stop
