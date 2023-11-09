@extends('errors::illustrated-layout')

@section('title', __('Forbidden'))
@section('code', '406')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
