@extends('layouts.master')

@section('meta_title')
    Modification du tag #{{ $tag->id }}
@stop

@section('content')
    <h1>Modifier un tag</h1>

    {{ Form::model($tag, array('route' => array('tag_modify', $tag->id))) }}
        {{ Form::label('name', 'Nom du tag') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop