@extends('adminlte::page')

@section ('title_prefix', 'Review - ')

@section('content')
    <review-create item="{{json_encode($item)}}" oldreview="{{$oldreview}}"></review-create>
@endsection
