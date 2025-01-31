@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <post-email-single item="{{json_encode($item)}}"></post-email-single>
@stop
