@extends('layouts.master')

@section('meta_title')
    @if (isset($subscription))
        Modifier un abonnement
    @else
        Nouvel abonnement
    @endif
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>
                @if (isset($subscription))
                    Modifier un abonnement
                @else
                    Nouvel abonnement
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">
                    @if (isset($subscription))
                        {{ Form::model($subscription, array('route' => array('subscription_modify', $subscription->id))) }}
                    @else
                        {{ Form::open(array('route' => 'subscription_add')) }}
                    @endif

                    <div class="row">
                            <div class="col-md-6">
                                {{ Form::label('user_id', 'Client') }}
                                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), isset($subscription)?$subscription->user_id:null, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
                            </div>
                        <div class="col-md-6">
                            {{ Form::label('organisation_id', 'Organisation') }}
                            <p>{{ Form::select('organisation_id', Organisation::Select('Sélectionnez une organisation'), isset($subscription)?$subscription->organisation_id:null, array('id' => 'selectOrganisationId', 'class' => 'form-control')) }}</p>
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
                            {{ Form::label('subscription_kind_id', 'Type') }}
                            <p>{{ Form::select('subscription_kind_id', SubscriptionKind::selectAll(), isset($subscription)?$subscription->subscription_kind_id:'', array('class' => 'form-control')) }}</p>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('subscription_list') }}" class="btn btn-white">Annuler</a>
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
//            $('.timePicker').timepicker({'timeFormat': 'H:i', step: 5});


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