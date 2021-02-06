@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <author-create item="{{$item}}"></author-create>

@endsection
