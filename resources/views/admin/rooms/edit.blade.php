@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <room-create item="{{$item}}"></room-create>

@endsection
