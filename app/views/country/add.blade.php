@extends('layouts.master')

@section('meta_title')
    Ajouter un pays
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Ajouter un pays</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">

                        {{ Form::open(array('route' => 'country_add')) }}
                        {{ Form::label('name', 'Nom') }}
                        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('country_list') }}" class="btn btn-white">Annuler</a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>

            </div>
        </div>
        </div>
@stop