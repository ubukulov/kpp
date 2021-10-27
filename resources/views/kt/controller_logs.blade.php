@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row" data-app>
            <div class="col-md-12" style="padding: 0px;">
                @include('partials.kt_header')
            </div>

            <kt-controller-logs
                :container_task="{{ json_encode($container_task) }}"
                posted="{{ __('words.posted') }}"
                not_posted="{{ __('words.not_posted') }}"
                selected="{{ __('words.selected') }}"
                not_selected="{{ __('words.not_selected') }}"
                issued="{{ __('words.issued') }}"
            >

            </kt-controller-logs>
        </div>
    </div>
@stop
