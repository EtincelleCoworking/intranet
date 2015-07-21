@extends('layouts.master')

@section('meta_title')
    Ajouter une utilisation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Ajouter une utilisation</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                        {{ Form::open(array('route' => array('pasttime_add'))) }}
                    <div class="row">
                        <div class="col-md-2">
                            {{ Form::label('date_past', 'Date') }}
                            <p>{{ Form::text('date_past', date('d/m/Y'), array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('time_start', 'Début') }}
                            <p>{{ Form::text('time_start', null, array('class' => 'form-control timePicker')) }}</p>
                        </div>
                        <div class="col-md-2">
                            {{ Form::label('time_end', 'Fin') }}
                            <p>{{ Form::text('time_end', null, array('class' => 'form-control timePicker')) }}</p>
                        </div>

                        <div class="col-md-4">
                            {{ Form::label('ressource_id', 'Ressource') }}
                            <p>{{ Form::select('ressource_id', Ressource::SelectAll('Sélectionnez une ressource'), null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    @if (Auth::user()->role == 'superadmin')
                        <div class="row">
                            <div class="col-md-6">
                                {{ Form::label('user_id', 'Client') }}
                                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('class' => 'form-control', 'id' => 'userSelector')) }}</p>
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('invoice_id', 'Facture') }}
                                <p>{{ Form::select('invoice_id', Invoice::Select('Sélectionnez une facture'), null, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>
                    @else
                        {{ Form::hidden('user_id', Auth::user()->id) }}
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::label('comment', 'Commentaire') }}
                            <p>{{ Form::text('comment', null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
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
        });
    </script>
@stop