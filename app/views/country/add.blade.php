@extends('layouts.master')

@section('meta_title')
	Ajout d'un pays
@stop

@section('content')
	<h1>Nouveau pays</h1>

	{{ Form::open(array('route' => 'country_add')) }}
        {{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
		<p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
	{{ Form::close() }}
@stop