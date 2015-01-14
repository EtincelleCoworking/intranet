@extends('layouts.master')

@section('meta_title')
    Modification de la ressource #{{ $ressource->id }}
@stop

@section('content')
    <h1>Modifier une ressource</h1>

    {{ Form::model($ressource, array('route' => array('ressource_modify', $ressource->id))) }}
        <p>{{ Form::text('name') }}</p>
        <p>{{ Form::submit('Modifier') }}</p>
    {{ Form::close() }}
@stop