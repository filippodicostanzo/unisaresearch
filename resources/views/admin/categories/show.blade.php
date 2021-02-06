@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <category-show item="{{json_encode($item)}}"></category-show>
@stop
