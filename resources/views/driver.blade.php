@extends('layouts.app')
@section('content')
    <driver-component
    :categories_tc="{{ json_encode($categories_tc) }}"
    :body_types="{{ json_encode($body_types) }}"
    :directions="{{ json_encode($directions) }}"
    ></driver-component>
@stop
