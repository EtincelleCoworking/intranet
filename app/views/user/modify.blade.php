@extends('layouts.master')

@section('meta_title')
	Modification de {{ $user->fullname }}
@stop

@section('content')
	<h1>Modifier un utilisateur</h1>

	{{ Form::model($user, array('route' => array('user_modify', $user->id))) }}
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
		<p>{{ Form::submit('Modifier') }}</p>
	{{ Form::close() }}

	<h2>Liste des organismes</h2>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($user->organisations as $orga)
			<tr>
				<td>{{ $orga->id }}</td>
				<td><a href="{{ URL::route('organisation_modify', $orga->id) }}">{{ $orga->name }}</a></td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<h2>Liste des factures</h2>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Date de création</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($user->invoices as $invoice)
			<tr>
				<td><a href="{{ URL::route('invoice_modify', $invoice->id) }}">{{ $invoice->ident }}</a></td>
				<td>{{ $invoice->created_at }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
@stop