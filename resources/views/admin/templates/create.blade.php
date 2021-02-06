@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <template-create title="{{$title}}"></template-create>
@stop

