@extends('layouts.master')

@section('meta_title')
    Abonnements
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des abonnements</h2>
        </div>
        <div class="col-sm-8">
            <div class="title-action">
                <a href="{{ URL::route('subscription_add') }}" class="btn btn-default">Ajouter un abonnement</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if(count($subscriptions) == 0)
        <div class="middle-box text-center animated fadeInRightBig">
            <h3 class="font-bold">Aucun abonnement</h3>

            <div class="error-desc">
                <br/><a href="{{ URL::route('subscription_add') }}" class="btn btn-primary m-t">Ajouter un
                    abonnement</a>
            </div>
        </div>
    @else


        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    @if(count($companies)>0)
                        <div class="ibox-title">
                            Renouveler les sociétés:
                            @foreach($companies as $company_id => $company_data)
                                <a href="{{ URL::route('subscription_renew_company', $company_id) }}"
                                   class="btn btn-xs btn-primary">{{$company_data['name']}}
                                    ({{$company_data['count']}})</a>
                            @endforeach
                        </div>
                    @endif
                    <div class="ibox-content">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>N°</th>
                                <th>Membre</th>
                                <th>Description</th>
                                <th>Echéance</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($subscriptions as $position => $subscription)
                                <tr>
                                    <td>{{$position + 1}}</td>
                                    <td>
                                        @if (Auth::user()->isSuperAdmin())
                                            <a href="{{ URL::route('organisation_modify', $subscription->organisation->id) }}">{{ $subscription->organisation->name }}</a>
                                            (
                                            <a href="{{ URL::route('user_modify', $subscription->user->id) }}">{{ $subscription->user->fullname }}</a>
                                            )
                                        @else
                                            {{ $subscription->organisation->name }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $subscription->caption }}
                                    </td>
                                    <td>
                                        @if ($subscription->daysBeforeRenew <= 0)
                                            <span class="badge badge-danger">
                                                  {{ date('d/m/Y', strtotime($subscription->renew_at)); }}
                        </span>
                                        @elseif ($subscription->daysBeforeRenew < 7)
                                            <span class="badge badge-warning">
                                                          {{ date('d/m/Y', strtotime($subscription->renew_at)); }}
                        </span>
                                        @else
                                            <span class="badge badge-success">
                                                                  {{ date('d/m/Y', strtotime($subscription->renew_at)); }}
                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('subscription_renew', $subscription->id) }}"
                                           class="btn btn-xs btn-default">Renouveler</a>
                                        <a href="{{ URL::route('subscription_modify', $subscription->id) }}"
                                           class="btn btn-xs btn-outline btn-default">Modifier</a>
                                        <a href="{{ URL::route('subscription_delete', $subscription->id) }}"
                                           class="btn btn-xs btn-outline btn-danger">Supprimer</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="5">{{ $subscriptions->links() }}</td>
                            </tr>
                            </tfoot>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

@stop