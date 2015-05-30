@extends('layouts.master')

@section('meta_title')
    @if (isset($subscription))
        Modifier un abonnement
    @else
        Ajout d'un abonnement
    @endif
@stop

@section('content')
    @if ($errors->has())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif


    @if (isset($subscription))
        <h1>Modifier un abonnement</h1>
        {{ Form::model($subscription, array('route' => array('subscription_modify', $subscription->id))) }}
    @else
        <h1>Nouvel abonnement</h1>
        {{ Form::open(array('route' => 'subscription_add')) }}
    @endif

    <div class="row">
        @if (Auth::user()->role == 'superadmin')
            <div class="col-md-6">
                {{ Form::label('user_id', 'Client') }}
                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), isset($subscription)?$subscription->user_id:null, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
            </div>
        @else
            {{ Form::hidden('user_id', Auth::user()->id) }}
        @endif
        <div class="col-md-6">
            {{ Form::label('organisation_id', 'Organisation') }}
            <p>{{ Form::select('organisation_id', Organisation::Select('Sélectionnez une organisation'), isset($subscription)?$subscription->organisation_id:null, array('id' => 'selectOrganisationId', 'class' => 'form-control')) }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {{ Form::label('caption', 'Intitulé') }}
            <p>{{ Form::text('caption', isset($subscription)?$subscription->caption:'', array('class' => 'form-control')) }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            {{ Form::label('renew_at', 'Echéance') }}
            <p>{{ Form::text('renew_at', date('d/m/Y', isset($subscription)?strtotime($subscription->renew_at):time()), array('class' => 'form-control datePicker')) }}</p>
        </div>
        <div class="col-md-4">
            {{ Form::label('duration', 'Fréquence') }}
            <p>{{ Form::select('duration', array('1 month' => 'Mensuel'), isset($subscription)?$subscription->duration:'', array('class' => 'form-control')) }}</p>
        </div>
        <div class="col-md-4">
            {{ Form::label('amount', 'Montant HT') }}
            <p>{{ Form::text('amount', isset($subscription)?$subscription->amount:'', array('class' => 'form-control')) }}</p>
        </div>
    </div>

    @if (isset($subscription))
        {{ Form::submit('Modifier', array('class' => 'btn btn-lg btn-success')) }}
    @else
        {{ Form::submit('Ajouter', array('class' => 'btn btn-lg btn-success')) }}
    @endif
    {{ Form::close() }}
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
            $('.timePicker').timepicker({'timeFormat': 'H:i', step: 5});


            function getListOrganisations(id) {
                var oldOrganisation = $('#selectOrganisationId').val();
                var url = "{{ URL::route('user_json_organisations') }}";
                var urlFinale = url.replace("%7Bid%7D", id);

                $('#selectOrganisationId').html('');
                $.getJSON(urlFinale, function (data) {
                    var items = '';
                    $.each(data, function (key, val) {
                        if (oldOrganisation == key) {
                            items = items + '<option value="' + key + '" selected>' + val + '</option>';
                        } else {
                            items = items + '<option value="' + key + '">' + val + '</option>';
                        }
                    });

                    $('#selectOrganisationId').html(items);
                });
            }

            $('#selectUserId')
                    .select2()
                    .on('change', function (e) {
                        getListOrganisations($(this).val());
                    });

//            getListOrganisations($('#selectUserId').val());
        });
    </script>
@stop