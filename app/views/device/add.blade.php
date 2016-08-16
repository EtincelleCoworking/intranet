@extends('layouts.master')

@section('meta_title')
    Ajouter un périphérique
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Ajouter un périphérique</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">

                        @if (isset($device))
                            {{ Form::model($device, array('route' => array('device_modify', $device->id))) }}
                        @else
                            {{ Form::open(array('route' => 'device_add')) }}
                        @endif
                        <div class="col-md-6">
                            {{ Form::label('name', 'Nom') }}
                            <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('mac', 'Adresse MAC') }}
                            <p>{{ Form::text('mac', null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::label('user_id', 'Membre') }}
                            <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), isset($device)?$device->user_id:null, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="row">

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('device_list') }}" class="btn btn-white">Annuler</a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('#selectUserId').select2();
        });
    </script>
@stop