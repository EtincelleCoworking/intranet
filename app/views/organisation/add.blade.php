@extends('layouts.master')

@section('meta_title')
	Ajout d'une organisation
@stop

@section('content')
    @if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif

	<h1>Nouvelle organisation</h1>

	{{ Form::open(array('route' => 'organisation_add')) }}
        {{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('address', 'Adresse') }}
        <p>{{ Form::textarea('address', null, array('class' => 'form-control', 'rows' => 3)) }}</p>
        {{ Form::label('zipcode', 'Code postal') }}
        <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('city', 'Ville') }}
        <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
        {{ Form::label('country_id', 'Pays') }}
        <p>{{ Form::select('country_id', Country::Select(), 73, array('class' => 'form-control')) }}</p>
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
		<p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
	{{ Form::close() }}
@stop