@extends('layouts.master')

@section('meta_title')
	Connexion
@stop

@section('content')
    <div class="account-wrapper">
        <div class="account-body">
            <h3>Bienvenue Coworker !</h3>

            <h5><i class="fa fa-star"></i> Merci de renseigner ton adresse et mot de passe <i class="fa fa-star-o"></i></h5>

            {{ Form::open(array('route' => 'user_login_check', 'class' => 'form account-form')) }}

            <div class="form-group">
                <label for="email" class="placeholder-hidden">Adresse email</label>
                {{ Form::email('email', null, array('placeholder' => "Adresse email", 'class' => 'form-control')) }}
            </div> <!-- /.form-group -->

            <div class="form-group">
                <label for="login-password" class="placeholder-hidden">Password</label>
                {{ Form::password('password', array('class' => 'form-control')) }}
            </div> <!-- /.form-group -->

            <div class="form-group clearfix">
                <div class="pull-left">
                    <label class="checkbox-inline">
                        {{ Form::checkbox('remember') }} <small>Mémoriser la connexion</small>
                    </label>
                </div>

                <div class="pull-right">
                    <small><a href="{{ action('RemindersController@getRemind') }}">Mot de passe oublié?</a></small>
                </div>
            </div> <!-- /.form-group -->

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="4">
                    Connexion &nbsp; <i class="fa fa-play-circle"></i>
                </button>
            </div> <!-- /.form-group -->

            {{ Form::close() }}
    </div> <!-- /.account-body -->
@stop