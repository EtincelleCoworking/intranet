@extends('layouts.master')

@section('meta_title')
    Ajout d'une TVA
@stop

@section('content')
    <h1>Nouvelle TVA</h1>

    {{ Form::open(array('route' => 'vat_add')) }}
        {{ Form::label('value', 'Valeur de la TVA') }}
        <p>{{ Form::text('value', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop