@extends('layouts.master')

@section('meta_title')
    Ajout d'une ressource
@stop

@section('content')
    <h1>Nouvelle ressource</h1>

    {{ Form::open(array('route' => 'ressource_add')) }}
        <p>{{ Form::text('name') }}</p>
        <p>{{ Form::submit('Ajouter') }}</p>
    {{ Form::close() }}
@stop