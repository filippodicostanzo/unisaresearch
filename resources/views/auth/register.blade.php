@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.register_message'))

@php($countries = json_decode(Countries::getList('en', 'json'), true))

@section('auth_body')
    <form action="{{ $register_url }}" method="post">
        {{ csrf_field() }}
        <div class="row">
            {{-- Name field --}}
            <div class="input-group mb-3 col-md-6 col-sm-12">
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}" placeholder="{{ __('adminlte::adminlte.name') }}" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('name') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Name field --}}
            <div class="input-group mb-3 col-md-6 col-sm-126">
                <input type="text" name="surname" class="form-control {{ $errors->has('surname') ? 'is-invalid' : '' }}"
                       value="{{ old('surname') }}" placeholder="{{ __('adminlte::adminlte.surname') }}" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('surname'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('surname') }}</strong>
                    </div>
                @endif
            </div>

            <div class="input-group mb-3 col-md-6 col-sm-12">
                <select id="title" name="title"  class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}">
                    <option value="">Title</option>
                    <option value="Prof">Prof.</option>
                    <option value="Dr">Dr.</option>
                    <option value="Mr">Mr.</option>
                    <option value="Ms">Ms.</option>
                    <option value="Other">Other</option>
                </select>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-address-book {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('title'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('title') }}</strong>
                    </div>
                @endif
            </div>

            <div class="input-group mb-3 col-md-6 col-sm-12">
                <select id="gender" name="gender"  class="form-control {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                    <option value="">Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-mars {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>

                @if($errors->has('gender'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('gender') }}</strong>
                    </div>
                @endif

            </div>

            <div class="input-group mb-3 col-md-6 col-sm-12">
                <select id="country" name="country"  class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}">
                    <option value="">Country</option>
                    @foreach($countries  as $key => $value)
                      <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                </select>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-globe {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>

                @if($errors->has('country'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('country') }}</strong>
                    </div>
                @endif

            </div>

            {{-- City field --}}
            <div class="input-group mb-3 col-md-6 col-sm-12">
                <input type="text" name="city" class="form-control {{ $errors->has('city') ? 'is-invalid' : '' }}"
                       value="{{ old('city') }}" placeholder="{{ __('adminlte::adminlte.city') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-city {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('city'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('city') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Affiliation field --}}
            <div class="input-group mb-3 col-md-6 col-sm-12">
                <input type="text" name="affiliation"
                       class="form-control {{ $errors->has('affiliation') ? 'is-invalid' : '' }}"
                       value="{{ old('affiliation') }}" placeholder="{{ __('adminlte::adminlte.affiliation') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-asterisk {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('affiliation'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('affiliation') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Disciplinary field --}}
            <div class="input-group mb-3 col-md-6 col-sm-12">
                <input type="text" name="disciplinary"
                       class="form-control {{ $errors->has('disciplinary') ? 'is-invalid' : '' }}"
                       value="{{ old('disciplinary') }}" placeholder="{{ __('adminlte::adminlte.disciplinary') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-university {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('disciplinary'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('disciplinary') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Email field --}}
            <div class="input-group mb-3 col-12">
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Password field --}}
            <div class="input-group mb-3 col-12">
                <input type="password" name="password"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="{{ __('adminlte::adminlte.password') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('password'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('password') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Confirm password field --}}
            <div class="input-group mb-3 col-12">
                <input type="password" name="password_confirmation"
                       class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                       placeholder="{{ __('adminlte::adminlte.retype_password') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('password_confirmation'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </div>
                @endif
            </div>

            <div class="col-12">

                {{-- Register button --}}
                <button type="submit"
                        class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                    <span class="fas fa-user-plus"></span>
                    {{ __('adminlte::adminlte.register') }}
                </button>

            </div>
        </div>
    </form>

@stop

@section('auth_footer')
    <p class="my-0 text-center">
        <a href="{{ $login_url }}">
            {{ __('adminlte::adminlte.i_already_have_a_membership') }}
        </a>
    </p>
@stop
