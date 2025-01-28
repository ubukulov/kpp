@extends('layouts.app')
<style>
    .theme--light.v-input, .theme--light.v-input input, .theme--light.v-input textarea {
        font-size: 20px !important;
    }
    .qrcode-stream-wrapper[data-v-35411cc1] {
        height: 250px !important;
    }
</style>
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
