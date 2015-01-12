@extends('layouts.master')

@section('meta_title')
	Ajout d'une facture
@stop

@section('content')
	<h1>Nouvelle facture</h1>

	{{ Form::open(array('route' => 'invoice_add')) }}
		<p>{{ Form::select('user_id', User::Select('Sélectionnez un client')) }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop