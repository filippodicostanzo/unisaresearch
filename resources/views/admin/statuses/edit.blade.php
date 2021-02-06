@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <status-create item="{{$item}}"></status-create>

@endsection
