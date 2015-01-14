@extends('layouts.master')

@section('meta_title')
	Ajout d'un utilisateur
@stop

@section('content')
	<h1>Nouvel utilisateur</h1>

	{{ Form::open(array('route' => 'user_add')) }}
		<p>{{ Form::email('email') }}</p>
        <p>{{ Form::text('firstname') }}</p>
		<p>{{ Form::text('lastname') }}</p>
		<p>{{ Form::password('password', null) }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop