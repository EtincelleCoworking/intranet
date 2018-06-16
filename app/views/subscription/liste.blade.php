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
                <a href="{{ URL::route('subscription_add') }}" class="btn btn-primary">Ajouter un abonnement</a>
                <a href="{{ URL::route('subscription_overuse') }}" class="btn btn-default">Dépassements</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Filtre</h5>

                    {{--<div class="ibox-tools">--}}
                    {{--<a class="collapse-link">--}}
                    {{--<i class="fa fa-chevron-up"></i>--}}
                    {{--</a>--}}
                    {{--</div>--}}
                </div>
                <div class="ibox-content">

                    {{ Form::open(array('route' => array('subscription_list'))) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    <div class="row">
                        <div class="col-md-3">
                            {{ Form::select('filtre_organisation_id', Organisation::SelectAll('Sélectionnez une société'), Session::get('filtre_subscription.organisation_id') ? Session::get('filtre_subscription.organisation_id') : null, array('id' => 'filter-organisation','class' => 'form-control')) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_subscription.user_id') ? Session::get('filtre_subscription.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::select('filtre_city_id', City::SelectAll('Sélectionnez une ville'), Session::get('filtre_subscription.city_id') ? Session::get('filtre_subscription.city_id') : null, array('id' => 'filter-location','class' => 'form-control')) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{URL::route('subscription_filter_reset')}}" class="btn btn-sm btn-default">Réinitialiser</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>




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
                                <th>Ville</th>
                                <th>Type</th>
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
                                    <td>{{ $subscription->user->location->city->name }}</td>
                                    <td>
                                        @if($subscription->is_automatic_renew_enabled)
                                            <i class="fa fa-refresh"></i>
                                        @endif
                                        {{$subscription->kind->ressource->name}}
                                    </td>
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
                                                  {{ date('d/m/Y', strtotime($subscription->renew_at)) }}
                        </span>
                                        @elseif ($subscription->daysBeforeRenew < 7)
                                            <span class="badge badge-warning">
                                                          {{ date('d/m/Y', strtotime($subscription->renew_at)) }}
                        </span>
                                        @else
                                            <span class="badge badge-success">
                                                                  {{ date('d/m/Y', strtotime($subscription->renew_at)) }}
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


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('#filter-client').select2();
            $('#filter-organisation').select2();
            $('#filter-location').select2();
        });
    </script>
@stop
