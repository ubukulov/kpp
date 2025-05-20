@extends('layouts.app')
@section('content')
    <div class="row" data-app>
        <div class="col-md-12" style="padding: 0px;">
            @include('partials.kt_header')
        </div>

        <cargo-order-show
            :cargo-task="{{ json_encode($cargoTask) }}"
            :cargo-stocks="{{ json_encode($cargoStocks) }}"
            :user="{{ json_encode(Auth::user())  }}"
        ></cargo-order-show>
    </div>
@stop
