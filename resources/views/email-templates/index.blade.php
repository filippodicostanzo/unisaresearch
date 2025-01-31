@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert {{ session()->get('alert-class', 'alert-info') }} alert-dismissible fade show">
                {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>
    <email-template-table
        title="Template Email"
        :items="{{ $emailTemplates->toJson() }}"
    ></email-template-table>
@endsection
