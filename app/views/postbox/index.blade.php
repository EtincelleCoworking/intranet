@extends('layouts.master')

@section('meta_title')
    Domiciliation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Domiciliation</h2>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(count($organisations) == 0)
                        <p>Aucune domiciliation n'est associée à votre compte.</p>
                    @else
                        @if(Auth::user()->isSuperAdmin())
                            @if(count($error_organisations)>0)
                                <p>Les organisations suivantes n'ont pas d'abonnement :</p>
                                <ul>
                                    @foreach($error_organisations as $organisation)
                                        <li>
                                            <a href="{{URL::route('organisation_modify', $organisation->id)}}">{{$organisation->name}}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @foreach($subscriptions as $kind => $local_subscriptions)
                                <h2>{{$ressources[$kind]}}</h2>
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Organisation</th>
                                        <th>Contact</th>
                                        <th>Réexpédition</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>Abonnement</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($local_subscriptions as $subscription)
                                        <tr
                                                @if($organisations[$subscription->organisation_id]->domiciliation_end_at && ($organisations[$subscription->organisation_id]->domiciliation_end_at < date('Y-m-d')))
                                                class="text-muted"
                                                @endif
                                        >
                                            <td>
                                                @if(Auth::user()->isSuperAdmin())
                                                    <a href="{{URL::route('organisation_modify', $organisations[$subscription->organisation_id]->id)}}">{{$organisations[$subscription->organisation_id]->name}}</a>
                                                @else
                                                    {{$organisations[$subscription->organisation_id]->name}}
                                                @endif
                                            </td>
                                            <td>
                                                @if($organisations[$subscription->organisation_id]->accountant_id)
                                                    <a href="{{URL::route('user_modify', $organisations[$subscription->organisation_id]->accountant->id)}}">{{$organisations[$subscription->organisation_id]->accountant->fullname}}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{$organisations[$subscription->organisation_id]->getDomiciliationFrequency()}}
                                            </td>
                                            <td>
                                                @if($organisations[$subscription->organisation_id]->domiciliation_start_at)
                                                    {{date('d/m/Y', strtotime($organisations[$subscription->organisation_id]->domiciliation_start_at))}}
                                                @endif
                                            </td>
                                            <td>
                                                @if($organisations[$subscription->organisation_id]->domiciliation_end_at)
                                                    {{date('d/m/Y', strtotime($organisations[$subscription->organisation_id]->domiciliation_end_at))}}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($organisations[$subscription->organisation_id]->domiciliation_end_at && ($organisations[$subscription->organisation_id]->domiciliation_end_at < date('Y-m-d')))
                                                @else
                                                        @if($subscription->is_automatic_renew_enabled)
                                                            <i class="fa fa-refresh"
                                                               title="Renouvellement automatique"></i>
                                                        @endif
                                                        {{date('d/m/Y', strtotime($local_subscriptions[$organisations[$subscription->organisation_id]->id]->renew_at))}}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ URL::route('postbox_details', $organisations[$subscription->organisation_id]->id) }}"
                                                   class="btn btn-default btn-xs">Historique</a>
                                                <a href="{{ URL::route('postbox_notify', $organisations[$subscription->organisation_id]->id) }}"
                                                   class="btn btn-primary btn-xs">Notifier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        @else
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Organisation</th>
                                    <th>Contact</th>
                                    <th>Réexpédition</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($organisations as $organisation)
                                    <tr
                                            @if($organisation->domiciliation_end_at && ($organisation->domiciliation_end_at < date('Y-m-d')))
                                            class="text-muted"
                                            @else
                                            @if(!isset($subscriptions[$organisation->id]) || !$subscriptions[$organisation->id]->is_automatic_renew_enabled)
                                            class="bg-danger"
                                            @endif
                                            @endif
                                    >
                                        <td>
                                            {{$organisation->name}}
                                        </td>
                                        <td>
                                            @if($organisation->accountant_id)
                                                <a href="{{URL::route('user_profile', $organisation->accountant->id)}}">{{$organisation->accountant->fullname}}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            {{$organisation->getDomiciliationFrequency()}}
                                        </td>
                                        <td>
                                            @if($organisation->domiciliation_start_at)
                                                {{date('d/m/Y', strtotime($organisation->domiciliation_start_at))}}
                                            @endif
                                        </td>
                                        <td>
                                            @if($organisation->domiciliation_end_at)
                                                {{date('d/m/Y', strtotime($organisation->domiciliation_end_at))}}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>

                                            <a href="{{ URL::route('postbox_details', $organisation->id) }}"
                                               class="btn btn-default btn-xs">Historique</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif


                    @endif
                </div>
            </div>
        </div>
    </div>
@stop