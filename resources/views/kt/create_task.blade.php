@extends('layouts.app')
@push('styles')
    <style>
        .kt{
            width: 900px;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Создать заявку</h4>

                <kt-operator-task-create></kt-operator-task-create>

                {{--<form action="{{ route('receive.container') }}" method="post" enctype="multipart/form-data">
                    @csrf


                </form>--}}
            </div>
        </div>
    </div>
@stop