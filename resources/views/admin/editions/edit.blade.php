@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <edition-create item="{{$item}}"></edition-create>

@endsection
