@extends('layouts.master')

@section('meta_title')
	Ajout d'un utilisateur
@stop

@section('content')
	<h1>Nouvel utilisateur</h1>

	{{ Form::open(array('route' => 'user_add')) }}
        {{ Form::label('email', 'Adresse email') }}
		<p>{{ Form::email('email') }}</p>
        {{ Form::label('firstname', 'Prénom') }}
        <p>{{ Form::text('firstname') }}</p>
        {{ Form::label('lastname', 'Nom de famille') }}
		<p>{{ Form::text('lastname') }}</p>
        {{ Form::label('password', 'Mot de passe') }}
		<p>{{ Form::password('password', null) }}</p>
        {{ Form::label('bio_short', 'Courte bio') }}
        <p>{{Form::textarea('bio_short') }}</p>
        {{ Form::label('bio_long', 'Longue bio') }}
        <p>{{Form::textarea('bio_long') }}</p>
        {{ Form::label('is_member', 'Est membre ?') }}
        <p>{{ Form::checkbox('is_member', true) }}</p>
        {{ Form::label('twitter', 'Twitter') }}
        <p>{{ Form::text('twitter') }}</p>
        {{ Form::label('website', 'Site internet') }}
        <p>{{ Form::text('website') }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop