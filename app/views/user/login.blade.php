@extends('layouts.master')

@section('meta_title')
	Connexion
@stop

@section('content')
	<h1>Se connecter</h1>

	{{ Form::open(array('route' => 'user_login_check')) }}
		<p>{{ Form::email('email', null, array('placeholder' => "Adresse email")) }}</p>
		<p>{{ Form::password('password', null) }}</p>
		<p>{{ Form::checkbox('remember') }} mémoriser la connexion</p>
		<p>{{ Form::submit('Connexion') }}</p>
	{{ Form::close() }}
@stop