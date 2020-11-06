@extends('adminlte::page')

@section ('title_prefix', 'Categories - ')

@section('content')
    <category-create item="{{$item}}"></category-create>

@endsection
