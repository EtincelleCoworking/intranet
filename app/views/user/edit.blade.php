@extends('layouts.master')

@section('meta_title')
    Modification de mon profil
@stop

@section('content')
    <h1>Modifier mon profil</h1>

    {{ Form::model($user, array('route' => array('user_edit'),'files' => true)) }}
        <ul id="tabUserAdd" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#connexion" aria-controls="connexion" role="tab" data-toggle="tab">Informations de connexion</a>
            </li>
            <li role="presentation">
                <a href="#bio" aria-controls="bio" role="tab" data-toggle="tab">Biographie</a>
            </li>
            <li role="presentation">
                <a href="#socials" aria-controls="socials" role="tab" data-toggle="tab">Réseaux sociaux</a>
            </li>
            <li role="presentation">
                <a href="#competence" aria-controls="competence" role="tab" data-toggle="tab">Compétences</a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="connexion">
                {{ Form::label('email', 'Adresse email') }}
                <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('firstname', 'Prénom') }}
                <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('lastname', 'Nom de famille') }}
                <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('password', 'Mot de passe') }}
                <p>{{ Form::password('password', array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="bio">
                {{ Form::label('bio_short', 'Courte bio') }}
                <p>{{Form::textarea('bio_short', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('bio_long', 'Longue bio') }}
                <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="socials">
                {{ Form::label('avatar', 'Avatar') }}
                <p>{{ Form::file('avatar', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('twitter', 'Twitter') }}
                <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('website', 'Site internet') }}
                <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('phone', 'Téléphone') }}
                <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="competence">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-9">
                                {{ Form::label('competence1_title', 'Compétence 1') }}
                                <p>{{ Form::text('competence1_title', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('competence1_value', 'Valeur') }}
                                <p>{{ Form::number('competence1_value', null, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-9">
                                {{ Form::label('competence2_title', 'Compétence 2') }}
                                <p>{{ Form::text('competence2_title', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('competence2_value', 'Valeur') }}
                                <p>{{ Form::number('competence2_value', null, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-9">
                                {{ Form::label('competence3_title', 'Compétence 3') }}
                                <p>{{ Form::text('competence3_title', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('competence3_value', 'Valeur') }}
                                <p>{{ Form::number('competence3_value', null, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-9">
                                {{ Form::label('competence4_title', 'Compétence 4') }}
                                <p>{{ Form::text('competence4_title', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-3">
                                {{ Form::label('competence4_value', 'Valeur') }}
                                <p>{{ Form::number('competence4_value', null, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div align="center">
            {{ Form::submit('Modifier mon profil', array('class' => 'btn btn-success')) }}
        </div>
    {{ Form::close() }}
@stop