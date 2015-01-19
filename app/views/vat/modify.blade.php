@extends('layouts.master')

@section('meta_title')
    Modification de la TVA #{{ $vat->id }}
@stop

@section('content')
    <h1>Modifier une TVA</h1>

    {{ Form::model($vat, array('route' => array('vat_modify', $vat->id))) }}
        {{ Form::label('value', 'Nom de la TVA') }}
        <p>{{ Form::text('value', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop