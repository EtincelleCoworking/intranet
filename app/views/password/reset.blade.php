@extends('layouts.master')

@section('content')
    <div class="col-sm-offset-4 col-sm-4">
        <br>
        @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif
        <div class="panel panel-primary">
            <div class="panel-heading">Création d'un nouveau mot de passe</div>
            <div class="panel-body">
                <div class="col-sm-12">
                    {{ Form::open(array('action' => 'RemindersController@postReset', 'method' => 'post', 'class' => 'form-horizontal panel')) }}
                      {{ Form::hidden('token', $token) }}
                      <div class="form-group">
                        {{ Form::email('email', null, array('class' => 'form-control', 'placeholder' => 'Adresse email')) }}
                      </div>
                      <div class="form-group">
                        {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Mot de passe')) }}
                      </div>
                      <div class="form-group">
                        {{ Form::password('password_confirmation', array('class' => 'form-control', 'placeholder' => 'Confirmation mot de passe')) }}
                      </div>
                      {{ Form::submit('Envoyer', array('class' => 'btn btn-primary pull-right')) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@stop