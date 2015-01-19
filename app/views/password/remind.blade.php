@extends('layouts.master')

@section('content')
    <div class="col-sm-offset-4 col-sm-4">
        <br>
        @if(Session::has('status'))
            <div class="alert alert-success">{{ Session::get('status') }}</div>
        @else
            <div class="panel panel-primary">
                <div class="panel-heading">Oubli du mot de passe, entrez votre email :</div>
                <div class="panel-body">
                    <div class="col-sm-12">
                        {{ Form::open(array('action' => 'RemindersController@postRemind', 'method' => 'post', 'class' => 'form-horizontal panel')) }}
                            <small class="text-danger">{{ Session::get('error') }}</small>
                          <div class="form-group {{ $errors->has('error') ? 'has-error' : '' }}">
                            {{ Form::email('email', null, array('class' => 'form-control', 'placeholder' => 'Adresse email')) }}
                          </div>
                            {{ Form::submit('Envoyer', array('class' => 'btn btn-primary pull-right')) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <a href="javascript:history.back()" class="btn btn-primary">
                <span class="glyphicon glyphicon-circle-arrow-left"></span> Retour
            </a>
        @endif
    </div>
@stop