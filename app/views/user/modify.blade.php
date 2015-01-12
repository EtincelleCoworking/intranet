@extends('layouts.master')

@section('meta_title')
	Modification de {{ $user->fullname }}
@stop

@section('content')
	<h1>Modifier un utilisateur</h1>

	{{ Form::model($user, array('route' => array('user_modify', $user->id))) }}
		<p>{{ Form::email('email') }}</p>
		<p>{{ Form::text('fullname') }}</p>
		<p>{{ Form::password('password', null) }}</p>
		<p>{{ Form::submit('Modifier') }}</p>
	{{ Form::close() }}
@stop