@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')
                <h4 class="text-center">Создать заявку на маркировку</h4>

                <form method="post" action="{{ route('mark.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="file" name="upload_file" required class="form-control-file" id="customFile">
                            </div>
                        </div>

                        <div class="col-md-6 text-left">
                            <div class="form-group">
                                <a class="btn btn-primary" href="{{ route('mark.index') }}">Назад</a>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button class="btn btn-success" type="submit">Создать заявку</button>
                            </div>
                        </div>
                    </div>

                </form>

                {{--<div class="alert alert-success" role="alert">
                    Файл успешно отправлен!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>--}}
            </div>
        </div>
    </div>
@stop
