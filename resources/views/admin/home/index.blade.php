@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@php

    use App\Models\Post;
    $items=Post::where('state','!=', '1')->orderBy('id', 'DESC')->limit(20)->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();
    $user=Auth::user();
    $roles = $user->roles()->first();

@endphp

@section('content')
    <div class="row">
        <div class="col-4">
            <card-widget title="Total User" type="users" data="{{App\Models\User::all()}}"
                         count="{{DB::table('users')->count()}}" icon="user"></card-widget>
        </div>
        <div class="col-4">
            <card-widget title="Total Posts" type="posts" data="{{App\Models\Post::all()}}"
                         count="{{DB::table('posts')->count()}}" icon="file-alt"></card-widget>
        </div>
        <div class="col-4">
            <card-widget title="Total Categories" type="categories" data="{{App\Models\Category::all()}}"
                         count="{{DB::table('categories')->count()}}" icon="tags"></card-widget>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$items}}" role="{{$roles}}"></card-table>
        </div>
    </div>
@stop
