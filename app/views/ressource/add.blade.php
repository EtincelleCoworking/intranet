@extends('layouts.master')

@section('meta_title')
    Ajout d'une ressource
@stop

@section('content')
    <h1>Nouvelle ressource</h1>

    {{ Form::open(array('route' => 'ressource_add')) }}
        {{ Form::label('name', 'Nom de la ressource') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop