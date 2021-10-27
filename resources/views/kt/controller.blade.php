@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row" data-app>
            <div class="col-md-12" style="padding: 0px;">
                @include('partials.kt_header')
            </div>

            <kt-controller name="{{ Auth::user()->full_name }}"></kt-controller>
        </div>
    </div>
@stop
