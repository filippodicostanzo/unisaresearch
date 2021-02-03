@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert {{ session()->get('alert-class', 'alert-info') }}">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <category-table title="{{$title}}" items="{{json_encode($items)}}"></category-table>
@stop
