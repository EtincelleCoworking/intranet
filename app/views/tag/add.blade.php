@extends('layouts.master')

@section('meta_title')
    Ajout d'un tag
@stop

@section('content')
    @if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif

    <h1>Nouveau tag</h1>

    {{ Form::open(array('route' => 'tag_add')) }}
        {{ Form::label('name', 'Nom du tag') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop