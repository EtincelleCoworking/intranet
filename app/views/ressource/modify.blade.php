@extends('layouts.master')

@section('meta_title')
    Modification de la ressource #{{ $ressource->id }}
@stop

@section('content')
    <h1>Modifier une ressource</h1>

    {{ Form::model($ressource, array('route' => array('ressource_modify', $ressource->id))) }}
        <div class="row">
            <div class="col-md-10">
                {{ Form::label('name', 'Nom de la ressource') }}
                <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
            </div>
            <div class="col-md-2">
                {{ Form::label('order_index', 'Ordre d\'affichage') }}
                <p>{{ Form::number('order_index', null, array('class' => 'form-control', 'min' => 1)) }}</p>
            </div>
        </div>
        <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop