@extends('layouts.master')

@section('meta_title')
    Modification du site {{$location->fullName}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-ls-12">
            <h2>Modification du site {{$location->fullName}}</h2>

        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    {{ Form::model($location, array('route' => array('location_modify', $location->id))) }}
                    <div class="row">
                        <div class="col-md-4">
                            {{ Form::label('city', 'Ville') }}
                            <p>{{ Form::select('city_id', City::SelectAll('-', true), null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('name', 'Nom') }}
                            <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('coworking_capacity', 'Nombre de postes en coworking') }}
                            <p>{{ Form::text('coworking_capacity', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('default_business_terms', 'Conditions commerciales par défaut') }}
                            <p>{{ Form::textarea('default_business_terms', null, array('class' => 'form-control', 'rows' => '15')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('sales_presentation', 'Présentation commerciale') }}
                            <p>{{ Form::textarea('sales_presentation', null, array('class' => 'form-control', 'rows' => '15')) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                {{ Form::checkbox('enabled', true) }}
                                {{ Form::label('enabled', 'Actif') }}
                            </p>
                        </div>
                        <div class="col-md-12">
                            {{ Form::label('welcome_email_content', 'Email de bienvenue') }}
                            <p>{{ Form::textarea('welcome_email_content', null, array('class' => 'form-control', 'rows' => '15')) }}</p>
                            <p class="text-muted">Les macros suivantes seront automatiquement remplacées :</p>
                            <ul class="text-muted">
                                <li>%User.Firstname%: Prénom</li>
                                <li>%User.Lastname%: Nom de famille</li>
                                <li>%User.Email%: Email</li>
                            </ul>


                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('location_list') }}" class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>
@stop