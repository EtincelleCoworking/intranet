@extends('layouts.master')

@section('meta_title')
	Ajout d'un organisme
@stop

@section('content')
	<h1>Nouvel organisme</h1>

	{{ Form::open(array('route' => 'organisation_add')) }}
        {{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name') }}</p>
        {{ Form::label('address', 'Adresse') }}
        <p>{{ Form::textarea('address') }}</p>
        {{ Form::label('zipcode', 'Code postal') }}
        <p>{{ Form::text('zipcode') }}</p>
        {{ Form::label('city', 'Ville') }}
        <p>{{ Form::text('city') }}</p>
        {{ Form::label('country_id', 'Pays') }}
        <p>{{ Form::select('country_id', Country::Select()) }}</p>
        {{ Form::label('tva_number', 'TVA') }}
		<p>{{ Form::text('tva_number') }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop