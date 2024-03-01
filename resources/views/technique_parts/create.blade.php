@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Вин Код</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Названия техники</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Кол-во запчастей</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
