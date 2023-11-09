@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row" data-app>
            <div class="col-md-12" style="padding: 0px;">
                @include('partials.kt_header')
            </div>

            <technique-controller name="{{ Auth::user()->full_name }}" token="{{ session()->get('token') }}"></technique-controller>
        </div>
    </div>
@stop
