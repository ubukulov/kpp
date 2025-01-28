@extends('layouts.app')
@push('styles')
    <style>
        .kt_header {
            height: 120px;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <v-app>
                    <v-container>
                        <div style="padding: 10px; text-align: center;">
                            <mark-manager :user="{{ json_encode(Auth::user()) }}"></mark-manager>
                        </div>
                    </v-container>
                </v-app>
            </div>
        </div>
    </div>
@stop

