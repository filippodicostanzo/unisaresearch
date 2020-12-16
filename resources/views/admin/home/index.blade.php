@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@php

    $items = \App\Models\Post::orderBy('id', 'DESC')->limit(20)->get();

               foreach ($items as $item) {

                   $postauthor = [];
                   $authors = explode(',', $item['authors']);
                   foreach ($authors as $author) {
                       array_push($postauthor, \App\Models\Author::where('id',$author)->first());
                   }
                   $item['json_authors'] = json_encode($postauthor,true);
                   $item['category'] = $item->category_fk->name;
                   $item['template'] = $item->template_fk->name;
                   $item['state'] = $item->state_fk->name;
                   $item['color'] = $item->state_fk->color;


           }

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
            <card-table data="{{$items}}"></card-table>
        </div>
    </div>
@stop
