@extends('layouts.master')

@section('meta_title')
    Modification du planning
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Modification du planning</h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Planning</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::model($item, array('route' => array('planning_modify_check', $item->id))) }}
                    <div class="row">
                        <div class="col-md-6 col-xs-12">
                            <div>
                                {{ Form::label('title', 'Membre') }}
                                <p>{{ Form::select('user_id', User::staff()->Select('Sélectionnez un membre', 'fullname'), $item->user_id, array('id' => 'planning-user','class' => 'form-control')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div>
                                {{ Form::label('title', 'Site') }}
                                <p>{{ Form::select('location_id', Location::SelectAll('Sélectionnez un site'), $item->location_id, array('id' => 'planning-location','class' => 'form-control')) }}</p>
                            </div>
                        </div>

                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <div class="col-xs-4">
                                    {{ Form::label('date', 'Date') }}
                                    <p>{{ Form::text('date', date('d/m/Y', strtotime($item->start_at)), array('class' => 'form-control datePicker')) }}</p>
                                    {{ Form::checkbox('is_holiday', true, $item->is_holiday, array('id' => 'checkbox_is_holiday')) }}
                                    <label for="checkbox_is_holiday">Vacances</label>
                                </div>
                                <div class="col-xs-4">
                                    {{ Form::label('start', 'Début') }}
                                    <p>{{ Form::select('start', Booking::selectableHours(), date('H:i', strtotime($item->start_at)), array('class' => 'form-control')) }}</p>
                                </div>
                                <div class="col-xs-4">
                                    {{ Form::label('end', 'Fin') }}
                                    <p>{{ Form::select('end', Booking::selectableHours(), date('H:i', strtotime($item->end_at)), array('class' => 'form-control')) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                        </div>

                    </div>

                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <a href="{{ URL::route('planning_delete', $item->id) }}" class="btn btn-danger pull-right">Supprimer</a>
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('planning_list') }}" class="btn btn-white">Annuler</a>
                        </div>
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

            $('#planning-user').select2();
            $('#planning-location').select2();
            $('.datePicker').datepicker();


        });
    </script>
@stop
