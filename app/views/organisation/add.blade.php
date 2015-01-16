@extends('layouts.master')

@section('meta_title')
	Ajout d'une organisation
@stop

@section('content')
	<h1>Nouvelle organisation</h1>

	{{ Form::open(array('route' => 'organisation_add')) }}
        {{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('address', 'Adresse') }}
        <p>{{ Form::textarea('address', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('zipcode', 'Code postal') }}
        <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('city', 'Ville') }}
        <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('country_id', 'Pays') }}
        <p>{{ Form::select('country_id', Country::Select(), null, array('class' => 'form-control')) }}</p>
        {{ Form::label('tva_number', 'TVA') }}
		<p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
		<p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
	{{ Form::close() }}
@stop