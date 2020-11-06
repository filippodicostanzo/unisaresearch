@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-4">
            <card-widget title="Total User" data="{{App\Models\User::all()}}" count="{{DB::table('users')->count()}}" icon="user"></card-widget>
        </div>
        <div class="col-4">
            <card-widget title="Total Documents" count="{{DB::table('users')->count()}}" icon="file-alt"></card-widget>
        </div>
        <div class="col-4">
            <card-widget></card-widget>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <card-table></card-table>
        </div>
    </div>
@stop
