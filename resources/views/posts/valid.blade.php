@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section ('title_prefix', 'Author - ')

@section('content')
    <post-validate item="{{json_encode($item)}}" reviews="{{json_encode($reviews)}}" status="{{json_encode($status)}}" comment="{{json_encode($comment)}}"></post-validate>
@endsection
