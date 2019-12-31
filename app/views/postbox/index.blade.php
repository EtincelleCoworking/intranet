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
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($organisations as $organisation)
                                <tr
                                        @if($organisation->domiciliation_end_at && ($organisation->domiciliation_end_at < date('Y-m-d')))
                                        class="text-muted"
                                        @endif
                                >
                                    <td class="col-md-3">
                                        @if(Auth::user()->isSuperAdmin())
                                            <a href="{{URL::route('organisation_modify', $organisation->id)}}">{{$organisation->name}}</a>
                                        @else
                                            {{$organisation->name}}
                                        @endif
                                    </td>
                                    <td class="col-md-3">
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
                                    <td class="col-md-2">
                                        {{$organisation->getDomiciliationFrequency()}}
                                    </td>
                                    <td class="col-md-1">
                                        @if($organisation->domiciliation_start_at)
                                            {{date('d/m/Y', strtotime($organisation->domiciliation_start_at))}}
                                        @endif
                                    </td>
                                    <td class="col-md-1">
                                        @if($organisation->domiciliation_end_at)
                                            {{date('d/m/Y', strtotime($organisation->domiciliation_end_at))}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="col-md-4">
                                        @if(Auth::user()->isSuperAdmin())
                                            <a href="{{ URL::route('postbox_notify', $organisation->id) }}"
                                               class="btn btn-primary btn-xs">Notifier</a>
                                            <a href="{{ URL::route('postbox_details', $organisation->id) }}"
                                               class="btn btn-default btn-xs">Historique</a>
                                        @else
                                            <a href="{{ URL::route('postbox_details', $organisation->id) }}"
                                               class="btn btn-primary btn-xs">Historique</a>
                                        @endif
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