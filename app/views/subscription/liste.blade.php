@extends('layouts.master')

@section('meta_title')
    Abonnements
@stop

@section('content')
    <a href="{{ URL::route('subscription_add') }}" class="btn btn-primary pull-right">Ajouter un abonnement</a>
    <h1>Liste des abonnements</h1>






    @if(count($subscriptions)==0)
        <p>Aucun abonnement.</p>
    @else

        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Mois</th>
            @foreach ($pending as $period => $amount)
                    <td>{{$period}}</td>
            @endforeach
            </tr>
            </thead>
            <tbody>
                <th>Montant</th>
            @foreach ($pending as $period => $amount)
                    <td>
                        {{ $amount }}
                    </td>
            @endforeach
            </tbody>
        </table>


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
                        @if (Auth::user()->role == 'superadmin')
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
                        @elseif ($subscription->daysBeforeRenew < 7)
                                    <span class="badge badge-warning">
                        @else
                                            <span class="badge badge-success">
                        @endif
                                                {{ date('d/m/Y', strtotime($subscription->renew_at)); }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ URL::route('subscription_renew', $subscription->id) }}"
                           class="btn btn-sm btn-default">Renouveler</a>
                        <a href="{{ URL::route('subscription_modify', $subscription->id) }}"
                           class="btn btn-sm btn-default">Modifier</a>
                        <a href="{{ URL::route('subscription_delete', $subscription->id) }}"
                           class="btn btn-sm btn-danger">Supprimer</a>
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
@stop