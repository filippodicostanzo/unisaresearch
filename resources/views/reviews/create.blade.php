@extends('adminlte::page')

@section ('title_prefix', 'Author - ')

@section('content')
    <review-create item="{{json_encode($item)}}"></review-create>
@endsection
