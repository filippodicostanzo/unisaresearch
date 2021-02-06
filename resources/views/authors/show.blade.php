@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <author-show item="{{json_encode($item)}}"></author-show>
@stop
