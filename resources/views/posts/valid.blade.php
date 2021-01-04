@extends('adminlte::page')

@section ('title_prefix', 'Author - ')

@section('content')
    <post-validate item="{{json_encode($item)}}" reviews="{{json_encode($reviews)}}"></post-validate>
@endsection
