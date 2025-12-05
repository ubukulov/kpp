@extends('cabinet.cabinet-vue')
@section('content')
    <div class="container">
        <div class="row" data-app>
            <div class="col-md-12">
                <img src="/img/cont.png" alt="container" style="width: 100%;">
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
