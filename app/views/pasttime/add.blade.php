@extends('layouts.master')

@section('meta_title')
    Ajouter un temps passé
@stop

@section('content')
    <h1>Ajouter un temps passé</h1>

    {{ Form::open(array('route' => array('pasttime_add'))) }}
        <div class="row">
            <div class="col-md-2">
                {{ Form::label('date_past', 'Date du temps passé') }}
                <p>{{ Form::text('date_past', date('d/m/Y'), array('class' => 'form-control datePicker')) }}</p>
            </div>
            <div class="col-md-2">
                {{ Form::label('time_start', 'Heure d\'arrivée') }}
                <p>{{ Form::text('time_start', null, array('class' => 'form-control timePicker')) }}</p>
            </div>
            <div class="col-md-2">
                {{ Form::label('time_end', 'Heure de départ') }}
                <p>{{ Form::text('time_end', null, array('class' => 'form-control timePicker')) }}</p>
            </div>
            @if (Auth::user()->role == 'superadmin')
            <div class="col-md-2">
                {{ Form::label('user_id', 'Client') }}
                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('class' => 'form-control')) }}</p>
            </div>
            @else
            {{ Form::hidden('user_id', Auth::user()->id) }}
            @endif
            <div class="col-md-2">
                {{ Form::label('ressource_id', 'Ressource') }}
                <p>{{ Form::select('ressource_id', Ressource::SelectAll('Sélectionnez une ressource'), null, array('class' => 'form-control')) }}</p>
            </div>
            <div class="col-md-2">
                {{ Form::submit('Aouter', array('class' => 'btn btn-lg btn-success')) }}
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){
    $('.datePicker').datepicker();
    $('.timePicker').timepicker({ 'timeFormat': 'H:i', step: 5 });
});
</script>
@stop