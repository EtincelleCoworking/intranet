@extends('layouts.master')

@section('meta_title')
    Modifier une utilisation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Modifier une utilisation</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    {{ Form::model($time, array('route' => array('pasttime_modify', $time->id))) }}


                    <div class="row">
                        <div class="col-md-2">
                            {{ Form::label('date_past', 'Date') }}
                            <p>{{ Form::text('date_past', date('d/m/Y', strtotime($time->date_past)), array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('time_start', 'Début') }}
                            <p>{{ Form::text('time_start', date('H:i', strtotime($time->time_start)), array('class' => 'form-control timePicker')) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('time_end', 'Fin') }}
                            <p>{{ Form::text('time_end', date('H:i', strtotime($time->time_end)), array('class' => 'form-control timePicker')) }}</p>
                        </div>

                        <div class="col-md-6">
                            {{ Form::label('ressource_id', 'Ressource') }}
                            <p>{{ Form::select('ressource_id', Ressource::SelectAll('Sélectionnez une ressource', $time->ressource_id), null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('location_id', 'Site') }}
                            <p>{{ Form::select('location_id', Location::selectAll(), $time->location_id, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('comment', 'Commentaire') }}
                            <p>{{ Form::text('comment', null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    @if (Auth::user()->isSuperAdmin())
                        <div class="row">
                            <div class="col-md-6">
                                {{ Form::label('user_id', 'Client') }}
                                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), $time->user_id, array('class' => 'form-control', 'id' => 'userSelector')) }}</p>
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('organisation_id', 'Organisation') }}
                                <p>{{ Form::select('organisation_id', Organisation::SelectAll('Sélectionnez une organisation'), $time->organisation_id, array('class' => 'form-control', 'id' => 'organisationSelector')) }}</p>
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('invoice_id', 'Facture') }}
                                <p>{{ Form::select('invoice_id', Invoice::selectAll('Sélectionnez une facture', $time->user_id), $time->invoice_id, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-2">
                                <p>
                                    <br />{{ Form::checkbox('is_free', true, $time->is_free) }}
                                {{ Form::label('is_free', 'Gratuit') }}
                                </p>
                            </div>
                        </div>
                    @else
                        {{ Form::hidden('user_id', Auth::user()->id) }}
                    @endif
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-primary')) }}
                        <a href="{{ URL::route('pasttime_list') }}" class="btn btn-white">Annuler</a>
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
            $('.datePicker').datepicker();
            $('.timePicker').timepicker({'timeFormat': 'H:i', step: 5});
            $('#userSelector').select2();
            $('#organisationSelector').select2();
        });
    </script>
@stop