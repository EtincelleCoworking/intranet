@extends('layouts.master')

@section('meta_title')
    Gestion de mon abonnement
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Gestion de mon abonnement</h2>
        </div>
    </div>
@stop

@section('content')

    <div class="ibox float-e-margins">
        <div class="ibox-content">
            <div class="row">
                <div class="col-md-12">
                    <p>Sélectionnez ci-dessous la formule la plus adaptée à votre besoin.</p>
                </div>
                {{ Form::open(array('route' => array('subscription_manage'))) }}

                <div class="col-md-6">
                    <div class="col-md-12">
                        {{ Form::label('option', 'Formule') }}
                        <div>
                            {{ Form::radio('option_id', 0, $subscription?false:true, array('id' => 'option0')) }}
                            <label for="option0" style="font-weight: normal">Aucune</label>
                        </div>
                        @foreach($items as $option_id => $option)
                            <div>
                                {{ Form::radio('option_id', $option_id, $subscription?($subscription->subscription_kind_id == $option_id):false, array('id' => sprintf('option%d', $option_id))) }}
                                <label for="option{{$option_id}}" style="font-weight: normal">{{$option}}</label>

                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-12">
                        {{ Form::label('renew_at', 'Date de renouvellement') }}
                        {{ Form::text('renew_at', $subscription?date('d/m/Y', strtotime($subscription->renew_at)):date('d/m/Y'), array('class' => 'form-control datePicker')) }}
                    </div>
                </div>
                <div class="col-md-6">
                    {{ Form::checkbox('is_automatic_renew_enabled', true, $subscription?$subscription->is_automatic_renew_enabled:true, array('id' => 'is_automatic_renew_enabled')) }}
                    <label for="is_automatic_renew_enabled">Renouvellement automatique</label>
                    <p>En activant le renouvellement automatique, une nouvelle facture avec la
                        formule sélectionnée sera automatiquement éditée à la date indiquée.</p>
                    <p>Un email de rappel vous est automatiquement envoyé quelques jours avant
                        l'échéance pour vous prévenir et vous laisser la possibilité de faire une pause ou changer
                        la formule d'abonnement.</p>
                    <p>En cas de soucis vous pouvez toujours contacter l'équipe via <a
                                href="mailto:{{Config::get('etincelle.team_email')}}?subject=Abonnement">{{Config::get('etincelle.team_email')}}</a>.
                    </p>

                </div>

            </div>
            <div class="row">
                <div class="hr-line-dashed"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();

        });
    </script>
@stop
