@extends('layouts.master')

@section('meta_title')
	Ajout d'un organisme
@stop

@section('content')
	<h1>Nouvel organisme</h1>

	{{ Form::open(array('route' => 'organisation_add')) }}
		<p>{{ Form::text('name') }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop