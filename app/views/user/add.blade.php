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
                    <div class="row">
                        <div class="col-lg-6">
                            {{ Form::label('birthday', 'Date de naissance') }}
                            <p>{{ Form::text('birthday', null, array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            {{ Form::label('gender', 'Genre') }}
                            {{ Form::select('gender', User::getGenders(), Auth::user()->gender, array('class' => 'form-control')) }}
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
                            <i class="fa fa-phone"></i>
                            {{ Form::label('phone', 'Téléphone') }}
                            <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-envelope"></i>
                            {{ Form::label('email', 'Adresse email') }}
                            <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-globe"></i>
                            {{ Form::label('website', 'Site web') }}
                            <small class="text-muted">ex : http://www.coworking-toulouse.com</small>
                            <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-twitter"></i>
                            {{ Form::label('twitter', 'Twitter') }}
                            <small class="text-muted">ex : etincelle_tls</small>
                            <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-github"></i>
                            {{ Form::label('social_github', 'GitHub') }}
                            <small class="text-muted">ex : https://github.com/EtincelleCoworking</small>
                            <p>{{ Form::text('social_github', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-instagram"></i>
                            {{ Form::label('social_instagram', 'Instagram') }}
                            <small class="text-muted">ex : https://instagram.com/etincelle_tls/</small>
                            <p>{{ Form::text('social_instagram', null, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-lg-6">
                            <i class="fa fa-linkedin"></i>
                            {{ Form::label('social_linkedin', 'LinkedIn') }}
                            <p>{{ Form::text('social_linkedin', null, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-lg-6">
                            <i class="fa fa-facebook"></i>
                            {{ Form::label('social_facebook', 'Facebook') }}
                            <p>{{ Form::text('social_facebook', null, array('class' => 'form-control')) }}</p>
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
                            {{ Form::label('default_location_id', 'Espace habituel') }}
                            {{ Form::select('default_location_id', Location::SelectAll(false), Auth::user()->default_location_id, array('class' => 'form-control')) }}
                        </div>
                        <div class="col-lg-6">
                            {{ Form::checkbox('is_member', true) }}
                            {{ Form::label('is_member', 'Membre') }}
                        </div>
                    </div>
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

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
        });
    </script>
@stop
