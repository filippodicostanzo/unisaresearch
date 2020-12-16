@extends('adminlte::page')

@section('title', 'AdminLTE')

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

               }
@endphp

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    @role('user')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">Your account must be validated by an administrator</p>
                </div>
            </div>
        </div>
    </div>
    @endrole

    @role('researcher|supervisor')
    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$items}}" ></card-table>
        </div>
    </div>
    @endrole
@stop
