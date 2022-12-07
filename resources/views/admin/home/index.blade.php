@extends('adminlte::page')

@section('title', __('titles.nfos'))

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@php

    use App\Models\Post;
    use App\Models\User;
    use App\Models\Edition;
    $edition = Edition::where('active', 1)->first();
    $items=Post::where('state','!=', '1')->where('edition', $edition->id)->orderBy('id', 'DESC')->limit(20)->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();
    $user=Auth::user();
    $roles = $user->roles()->first();



@endphp

@section('content')
    <div class="row">
        <div class="col-4">
            <card-widget title="Users to Approve" type="users" data="{{App\Models\User::all()}}"
                         count="{{User::whereRoleIs('user')->count()}}" icon="user"></card-widget>
        </div>
        <div class="col-4">
            <card-widget title="Total Paper Submitted" type="posts" data="{{App\Models\Post::where('state', '!=', '1')->get()}}"
                         count="{{Post::where('state', '!=', '1')->where('edition', $edition->id)->count()}}" icon="file-alt"></card-widget>
        </div>
        <div class="col-4">
            <card-widget title="Total Topics" type="categories" data="{{App\Models\Category::all()}}"
                         count="{{DB::table('categories')->count()}}" icon="tags"></card-widget>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$items}}" role="{{$roles}}"></card-table>
        </div>
    </div>
@stop
