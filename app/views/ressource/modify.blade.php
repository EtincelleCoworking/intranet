@extends('layouts.master')

@section('meta_title')
    Modification de la ressource #{{ $ressource->id }}
@stop

@section('content')
    <h1>Modifier une ressource</h1>

    {{ Form::model($ressource, array('route' => array('ressource_modify', $ressource->id))) }}
        {{ Form::label('name', 'Nom de la ressource') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop