@extends('layouts.master')

@section('meta_title')
    Ajout d'une ressource
@stop

@section('content')
    @if ($errors->has())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <h1>Nouvelle ressource</h1>

    {{ Form::open(array('route' => 'ressource_add')) }}
    <div class="row">
        <div class="col-md-8">
            {{ Form::label('name', 'Nom de la ressource') }}
            <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        </div>
        <div class="col-md-2">
            {{ Form::label('amount', 'Valeur') }}
            <p>{{ Form::number('amount', null, array('class' => 'form-control')) }}</p>
        </div>
        <div class="col-md-2">
            {{ Form::label('order_index', 'Ordre d\'affichage') }}
            <p>{{ Form::number('order_index', null, array('class' => 'form-control', 'min' => 1)) }}</p>
        </div>
    </div>
    <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop