@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <category-create item="{{$item}}"></category-create>

@endsection
