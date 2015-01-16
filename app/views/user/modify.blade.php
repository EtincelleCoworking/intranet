@extends('layouts.master')

@section('meta_title')
	Modification de {{ $user->fullname }}
@stop

@section('content')
	<h1>Modifier un utilisateur</h1>

	{{ Form::model($user, array('route' => array('user_modify', $user->id))) }}
        <ul id="tabUserAdd" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#connexion" aria-controls="connexion" role="tab" data-toggle="tab">Informations de connexion</a>
            </li>
            <li role="presentation">
                <a href="#bio" aria-controls="bio" role="tab" data-toggle="tab">Biographie</a>
            </li>
            <li role="presentation">
                <a href="#socials" aria-controls="socials" role="tab" data-toggle="tab">Réseaux sociaux</a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="connexion">
                {{ Form::label('email', 'Adresse email') }}
                <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('firstname', 'Prénom') }}
                <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('lastname', 'Nom de famille') }}
                <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('password', 'Mot de passe') }}
                <p>{{ Form::password('password', array('class' => 'form-control')) }}</p>
                <div class="checkbox">
                    {{ Form::label('is_member', 'Il est membre') }}
                    {{ Form::checkbox('is_member', true) }}
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="bio">
                {{ Form::label('bio_short', 'Courte bio') }}
                <p>{{Form::textarea('bio_short', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('bio_long', 'Longue bio') }}
                <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="socials">
                {{ Form::label('twitter', 'Twitter') }}
                <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('website', 'Site internet') }}
                <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
            </div>
        </div>

        <div align="center">
            {{ Form::submit('Modifier cet utilisateur', array('class' => 'btn btn-success')) }}
        </div>
	{{ Form::close() }}

	<h2>Liste de ses organisations</h2>
    <div class="row">
        <div class="col-md-6">
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
        </div>
        <div class="col-md-6">
            {{ Form::open(array('route' => array('organisation_user_add', $user->id))) }}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Ajouter dans une organisation</h3>
                    </div>
                    <div class="panel-body">
                        <p>{{ Form::select('organisation_id', Organisation::SelectNotInOrganisation($user->id, 'Sélectionnez une organisation'), null, array('class' => 'form-control')) }}</p>
                        <p>{{ Form::submit('Ajouter dans cette organisation', array('class' => 'btn btn-info')) }}</p>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>

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