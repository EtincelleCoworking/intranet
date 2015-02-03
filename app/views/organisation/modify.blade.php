@extends('layouts.master')

@section('meta_title')
	Modification de l'organisation #{{ $organisation->id }}
@stop

@section('content')
	<h1>Modifier une organisation</h1>

	{{ Form::model($organisation, array('route' => array('organisation_modify', $organisation->id))) }}
		{{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('address', 'Adresse') }}
        <p>{{ Form::textarea('address', null, array('class' => 'form-control', 'rows' => 3)) }}</p>
        {{ Form::label('zipcode', 'Code postal') }}
        <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('city', 'Ville') }}
        <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('country_id', 'Pays') }}
        <p>{{ Form::select('country_id', Country::Select(), null, array('class' => 'form-control')) }}</p>
        {{ Form::label('tva_number', 'TVA') }}
        <p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('code_purchase', 'Code achat') }}
                <p>{{ Form::text('code_purchase', null, array('class' => 'form-control')) }}</p>
            </div>
            <div class="col-md-6">
                {{ Form::label('code_sale', 'Code vente') }}
                <p>{{ Form::text('code_sale', null, array('class' => 'form-control')) }}</p>
            </div>
        </div>
		<p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
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
				<td>{{ Form::select('user_id', User::SelectNotInOrganisation($organisation->id, 'Sélectionnez un utilisateur'), null, array('class' => 'form-control')) }}</td>
				<td>{{ Form::submit('Ajouter', array('class' => 'btn btn-info')) }}</td>
			</tr>
		</tfoot>
	</table>
	{{ Form::close() }}
@stop