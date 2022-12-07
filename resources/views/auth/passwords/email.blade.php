@extends('adminlte::auth.passwords.email')

@section('auth_body')
    @parent

    @if(session('status'))
        <br>
        <p>It can sometimes take time for the email to come through. Please be patient and check your spam or trash folders.
            If after 24 hours you have not received your email you may have entered the wrong email address. In case of
            problems, please contact us at naplesforumonservice@gmail.com</p>
    @endif

@endsection

