@extends('layouts.master')

@section('meta_title')
	Modification de l'organisme #{{ $organisation->id }}
@stop

@section('content')
	<h1>Modifier un organisme</h1>

	{{ Form::model($organisation, array('route' => array('organisation_modify', $organisation->id))) }}
		{{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name') }}</p>
        {{ Form::label('address', 'Adresse') }}
        <p>{{ Form::textarea('address') }}</p>
        {{ Form::label('zipcode', 'Code postal') }}
        <p>{{ Form::text('zipcode') }}</p>
        {{ Form::label('city', 'Ville') }}
        <p>{{ Form::text('city') }}</p>
        {{ Form::label('country', 'Pays') }}
        <p>{{ Form::text('country') }}</p>
        {{ Form::label('tva_number', 'TVA') }}
        <p>{{ Form::text('tva_number') }}</p>
		<p>{{ Form::submit('Modifier') }}</p>
	{{ Form::close() }}

	<h2>Liste des membres</h2>
	@if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif
	{{ Form::model($organisation, array('route' => array('organisation_add_user', $organisation->id))) }}
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($organisation->users as $user)
			<tr>
				<td>{{ $user->id }}</td>
				<td><a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a></td>
				<td><a href="{{ URL::route('organisation_delete_user', array($organisation->id, $user->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer {{ $user->fullname }} ?" rel="nofollow">Retirer</a></td>
			</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td></td>
				<td>{{ Form::select('user_id', User::SelectNotInOrganisation($organisation->id, 'Sélectionnez un utilisateur')) }}</td>
				<td>{{ Form::submit('Ajouter') }}</td>
			</tr>
		</tfoot>
	</table>
	{{ Form::close() }}
@stop