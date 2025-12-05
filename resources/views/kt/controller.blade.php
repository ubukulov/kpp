@extends('cabinet.cabinet-vue')
@section('content')
    <div class="container">
        <div class="row" data-app>
            <div class="col-md-12">
                <img src="/img/cont.png" alt="container" style="width: 100%;">
            </div>

            <kt-controller name="{{ Auth::user()->full_name }}"></kt-controller>
        </div>
    </div>
@stop
