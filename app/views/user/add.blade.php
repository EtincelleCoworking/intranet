@extends('layouts.master')

@section('meta_title')
    Ajouter un membre
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Ajouter un membre</h2>
        </div>
    </div>
@stop

@section('content')
    {{ Form::open(array('route' => 'user_add', 'class' => 'form'), array()) }}
    <div class="row">
        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Etat civil</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            {{ Form::label('firstname', 'Prénom') }}
                            <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::label('lastname', 'Nom') }}
                            <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                        </div>

                    </div>


                </div>
            </div>

            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Contact</h5>
                </div>

                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            {{ Form::label('phone', 'Téléphone') }}
                            <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::label('email', 'Adresse email') }}
                            <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::label('website', 'Site internet') }}
                            <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                            <span class="help-block m-b-none">Exemple: http://www.coworking-toulouse.com</span>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::label('twitter', 'Twitter') }}
                            <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                            <span class="help-block m-b-none">Exemple: etincelle_tls</span>
                        </div>
                    </div>


                </div>
            </div>

            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Configuration</h5>
                </div>
                <div class="ibox-content">

                    <div class="row">
                        <div class="col-lg-6">
                            {{ Form::label('password', 'Mot de passe') }}
                            <p>{{ Form::password('password', array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::checkbox('is_member', true) }}
                            {{ Form::label('is_member', 'Membre') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Présentation</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::label('bio_short', 'Métier') }}
                    <p>{{Form::text('bio_short', null, array('class' => 'form-control')) }}</p>
                    {{ Form::label('bio_long', 'Présentation') }}
                    <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
        </div>




    </div>
    <div class="row">
        <div class="hr-line-dashed"></div>
        <div class="form-group">
            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
            <a href="{{ URL::route('user_list') }}" class="btn btn-white">Annuler</a>
        </div>

    </div>
    {{ Form::close() }}
@stop