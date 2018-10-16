@extends('layouts.master')

@section('meta_title')
    Modification de la ressource {{$ressource->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-ls-12">
            <h2>Modification de la ressource {{$ressource->name}}</h2>

        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    {{ Form::model($ressource, array('route' => array('ressource_modify', $ressource->id))) }}
                    <div class="row">
                        <div class="col-md-4">
                            {{ Form::label('name', 'Nom') }}
                            <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-8">
                            {{ Form::label('description', 'Description') }}
                            <p>{{ Form::text('description', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('location', 'Site') }}
                            <p>{{ Form::select('location_id', Location::SelectAll('-', true), null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-8">
                            {{ Form::label('subscription', 'Abonnement') }}
                            <p>{{ Form::select('subscription_id', Subscription::SelectPrivateOffices(), null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('kind', 'Type') }}
                            <p>{{ Form::select('ressource_kind_id', RessourceKind::SelectAll(), null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-8">
                            {{ Form::label('url', 'URL') }}
                            <p>{{ Form::text('url', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('amount', 'Valeur') }}
                            <p>{{ Form::text('amount', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-2">
                            <p>
                                {{ Form::checkbox('is_bookable', true) }}
                                {{ Form::label('is_bookable', 'Réservable') }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p>
                                {{ Form::checkbox('has_paper_summary', true) }}
                                {{ Form::label('has_paper_summary', 'Imprimer l\'affiche') }}
                            </p>
                            <p>
                                {{ Form::checkbox('intercom_enabled', true) }}
                                {{ Form::label('intercom_enabled', 'Contrôle l\'interphone') }}
                            </p>

                        </div>
                        <div class="col-md-2">
                            {{ Form::label('order_index', 'Ordre d\'affichage') }}
                            <p>{{ Form::text('order_index', null, array('class' => 'form-control', 'min' => 1)) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('booking_background_color', 'Couleur de fond') }}
                            <p>
                                {{ Form::text('booking_background_color', null, array('class' => 'form-control')) }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            {{ Form::label('sales_presentation', 'Présentation commerciale') }}
                            <p>{{ Form::textarea('sales_presentation', null, array('class' => 'form-control', 'rows' => '15')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('google_calendar_id', 'Synchronisation Google Calendar') }}
                            <p>{{ Form::text('google_calendar_id', null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('ressource_list') }}" class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>
@stop