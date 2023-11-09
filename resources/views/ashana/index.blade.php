@extends('layouts.kitchen')
@section('content')
    <kitchen-component
        :user="{{json_encode(Auth::user())}}"
        :companies="{{ json_encode($companies) }}"
    ></kitchen-component>
@stop
