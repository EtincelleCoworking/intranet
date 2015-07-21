@extends('layouts.master')

@section('meta_title')
    Modification de l'étiquette {{$tag->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Modification de l'étiquette {{$tag->name}}</h2>
        </div>

    </div>
@stop

@section('content')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    {{ Form::model($tag, array('route' => array('tag_modify', $tag->id))) }}
                    <div class="row">
                        {{ Form::label('name', 'Nom') }}
                        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('tag_list') }}" class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>


@stop