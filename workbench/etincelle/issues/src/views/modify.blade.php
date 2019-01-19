@extends('layouts.master')

@section('meta_title')
    @if($item->id)
        Modification d'une tâche
    @else
        Création d'une tâche
    @endif
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                @if($item->id)
                    Modification d'une tâche
                @else
                    Création d'une tâche
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="">
            <div class="ibox ">
                {{--<div class="ibox-title">--}}
                {{--<h5>Réservation</h5>--}}
                {{--</div>--}}
                <div class="ibox-content">
                    @if($item->id)
                        {{ Form::model($item, array('route' => array('issues_modify_check', $item->id), 'method' => 'POST')) }}
                    @else
                        {{ Form::model($item, array('route' => array('issues_create'), 'method' => 'POST')) }}
                    @endif

                    <div class="col-md-6 col-xs-12">
                        <div>
                            {{ Form::label('location_id', 'Localisation') }}
                            <p>{{ Form::select('location_id', Location::SelectAll('Sélectionnez une localisation', true), $item->location_id, array('id' => 'issue-location','class' => 'form-control')) }}</p>
                        </div>
                        <div>
                            {{ Form::label('title', 'Titre') }}
                            <p>{{ Form::text('title', $item->title, array('class' => 'form-control')) }}</p>
                        </div>

                        <div>
                            {{ Form::label('description', 'Description') }}
                            <p>{{ Form::textarea('description', $item->content, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    @if (Auth::user()->isSuperAdmin())
                        <div class="col-md-6 col-xs-12">
                            <div>
                                {{ Form::label('title', 'Client') }}
                                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), $item->user_id, array('id' => 'issue-user','class' => 'form-control')) }}</p>
                            </div>
                            <div>
                                {{ Form::label('title', 'Organisation') }}
                                <p>{{ Form::select('organisation_id', Organisation::SelectAll('Sélectionnez une organisation'), $item->organisation_id, array('id' => 'issue-organisation','class' => 'form-control')) }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('issues') }}"
                           class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('#issue-location').select2();
            $('#issue-user').select2();
            $('#issue-organisation').select2();
        });
    </script>
@stop
