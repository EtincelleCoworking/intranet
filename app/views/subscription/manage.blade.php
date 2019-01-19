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

    <div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-subscription">Mon abonnement</a></li>
            <li class=""><a data-toggle="tab" href="#tab-history">Historique</a></li>
        </ul>
        <div class="tab-content">
            <div id="tab-subscription" class="tab-pane active">
                <div class="panel-body">
                    <?php
                    $subscription_data = Subscription::getActiveSubscriptionInfos();
                    extract($subscription_data);
                    ?>

                    @if($active_subscription)
                        <div class="row">
                            <div class="col-md-12">

                                <h1 class="pull-right">
                                    Du {{date('d/m/Y', strtotime($active_subscription->subscription_from ))}}
                                    au {{date('d/m/Y', strtotime('-1 day', strtotime($active_subscription->subscription_to)))}}
                                </h1>
                                <h1>
                                    @if($subscription_used)
                                        @if ($subscription_used->hours)
                                            {{ $subscription_used->hours }} h
                                        @endif
                                        @if ($subscription_used->minutes)
                                            {{ $subscription_used->minutes }} min
                                        @endif
                                    @else
                                        0 h
                                    @endif
                                    @if($active_subscription->subscription_hours_quota > 0)
                                        / {{$active_subscription->subscription_hours_quota}} h
                                    @else
                                        / Illimité
                                    @endif
                                </h1>
                                @if($active_subscription->subscription_hours_quota > 0)
                                    <div class="progress ">
                                        <div style="width: {{$subscription_ratio}}%;" class="progress-bar
                                @if($subscription_ratio > 100)
                                                progress-bar-danger
@elseif($subscription_ratio>80)
                                                progress-bar-warning

@endif
                                                "></div>
                                    </div>
                                @endif

                                <p>Si vous avez une question concernant votre abonnement en cours, contactez-nous via <a
                                            href="mailto:{{Config::get('etincelle.team_email')}}?subject=Abonnement">{{Config::get('etincelle.team_email')}}</a>.


                                <div class="hr-line-dashed"></div>
                            </div>
                        </div>


                        {{ Form::open(array('route' => array('subscription_manage'))) }}
                        <div class="row">
                            <div class="col-md-12">
                                {{ Form::checkbox('is_automatic_renew_enabled', true, $subscription?$subscription->is_automatic_renew_enabled:false, array('id' => 'is_automatic_renew_enabled')) }}
                                <label for="is_automatic_renew_enabled">Renouvellement automatique</label>
                            </div>
                            <div id="norenew_content" style="display: none">
                                <div class="col-md-12">
                                    <p>Aucun renouvellement ne sera effectué.</p>
                                </div>
                            </div>
                            <div id="renew_content" style="display: none">
                                <div class="col-md-12">
                                    <p>En activant le renouvellement automatique, un nouvel abonnement sera
                                        automatiquement créé à la date indiquée.</p>
                                </div>
                                <div class="col-md-6">
                                    {{ Form::label('renew_at', 'Date de renouvellement') }}
                                    {{ Form::text('renew_at', $subscription?date('d/m/Y', strtotime($subscription->renew_at)):date('d/m/Y'), array('class' => 'form-control datePicker')) }}
                                </div>
                                <div class="col-md-6">
                                    {{ Form::label('option_id', 'Formule') }}
                                    @foreach($items as $option_id => $option)
                                        <div>
                                            {{ Form::radio('option_id', $option_id, $subscription?($subscription->subscription_kind_id == $option_id):false, array('id' => sprintf('option%d', $option_id))) }}
                                            <label for="option{{$option_id}}"
                                                   style="font-weight: normal">{{$option}}</label>

                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-12">
                                {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            </div>
                        </div>
                        {{ Form::close() }}
                    @else
                        {{ Form::open(array('route' => array('subscription_manage'))) }}
                        <div class="col-md-12">
                            <p>Vous n'avez pas d'abonnement en cours.</p>
                        </div>
                        <div class="col-md-12">
                            {{ Form::checkbox('is_automatic_renew_enabled', true, $subscription?$subscription->is_automatic_renew_enabled:false, array('id' => 'is_automatic_renew_enabled2')) }}
                            <label for="is_automatic_renew_enabled2">Activer la création d'un abonnement</label>
                        </div>
                        <div id="renew_content2" style="display:none">
                            <div class="col-md-6">
                                {{ Form::label('renew_at', 'Date de début') }}
                                {{ Form::text('renew_at', $subscription?date('d/m/Y', strtotime($subscription->renew_at)):date('d/m/Y'), array('class' => 'form-control datePicker')) }}
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('option_id', 'Formule') }}
                                @foreach($items as $option_id => $option)
                                    <div>
                                        {{ Form::radio('option_id', $option_id, $subscription?($subscription->subscription_kind_id == $option_id):false, array('id' => sprintf('option%d', $option_id))) }}
                                        <label for="option{{$option_id}}"
                                               style="font-weight: normal">{{$option}}</label>

                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-12">
                                {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            </div>

                        </div>
                        {{ Form::close() }}

                    @endif
                </div>
            </div>
            <div id="tab-history" class="tab-pane">
                <div class="panel-body">
                </div>
            </div>
        </div>
    </div>

@stop


@section('javascript')
    <script type="text/javascript">
        function update1() {
            if ($('#is_automatic_renew_enabled').is(':checked')) {
                $('#renew_content').show();
                $('#norenew_content').hide();
            } else {
                $('#renew_content').hide();
                $('#norenew_content').show();
            }
        }

        function update2() {
            if ($('#is_automatic_renew_enabled2').is(':checked')) {
                $('#renew_content2').show();
            } else {
                $('#renew_content2').hide();
            }
        }

        $().ready(function () {
            $('.datePicker').datepicker();
            $('#is_automatic_renew_enabled').change(update1);
            $('#is_automatic_renew_enabled2').change(update2);
            update1();
            update2();
        });
    </script>
@stop
