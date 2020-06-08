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
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Organisation</th>
                                <th>Contact</th>
                                <th>Réexpédition</th>
                                <th>Début</th>
                                <th>Fin</th>
                                @if(Auth::user()->isSuperAdmin())
                                    <th>Abonnement</th>
                                @endif
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
                                                class="table-warning"
                                            @endif
                                        @endif
                                >
                                    <td>
                                        @if(Auth::user()->isSuperAdmin())
                                            <a href="{{URL::route('organisation_modify', $organisation->id)}}">{{$organisation->name}}</a>
                                        @else
                                            {{$organisation->name}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($organisation->accountant_id)
                                            @if(Auth::user()->isSuperAdmin())
                                                <a href="{{URL::route('user_modify', $organisation->accountant->id)}}">{{$organisation->accountant->fullname}}</a>
                                            @else
                                                <a href="{{URL::route('user_profile', $organisation->accountant->id)}}">{{$organisation->accountant->fullname}}</a>
                                            @endif
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
                                    @if(Auth::user()->isSuperAdmin())
                                        <td>
                                            @if($organisation->domiciliation_end_at && ($organisation->domiciliation_end_at < date('Y-m-d')))
                                            @else
                                                @if(isset($subscriptions[$organisation->id]))
                                                    @if($subscriptions[$organisation->id]->is_automatic_renew_enabled)
                                                        <i class="fa fa-refresh" title="Renouvellement automatique"></i>
                                                    @endif
                                                    {{date('d/m/Y', strtotime($subscriptions[$organisation->id]->renew_at))}}
                                                @else
                                                    <i class="fa fa-times text-danger"></i>
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @if(Auth::user()->isSuperAdmin())
                                            <a href="{{ URL::route('postbox_notify', $organisation->id) }}"
                                               class="btn btn-primary btn-xs">Notifier</a>
                                        @endif
                                        <a href="{{ URL::route('postbox_details', $organisation->id) }}"
                                           class="btn btn-default btn-xs">Historique</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop