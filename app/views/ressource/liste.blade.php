@extends('layouts.master')

@section('meta_title')
    Liste des ressources
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des ressources</h2>
        </div>
        <div class="col-sm-8">
            <div class="title-action">
                <a href="{{ URL::route('ressource_add') }}" class="btn btn-primary">Ajouter une ressource</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @foreach($data as $group_name => $ressources)
            <div class="ibox">
                <div class="ibox-title">
                    <h5>{{$group_name}}</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Site</th>
                            <th>Nom</th>
                            <th>Client</th>
                            <!--
                            <th>Réservable</th>
                            -->
                            <th>Prix HT</th>
                            <!--
                            <th>Ordre</th>
                            -->
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($ressources as $n => $ressource)
                            <tr
                            <?php
                                if (in_array($ressource->ressource_kind_id, array(RessourceKind::TYPE_MEETING_ROOM, RessourceKind::TYPE_PRIVATE_OFFICE))
                                && (!$ressource->is_bookable && !$ressource->subscription_id)) {
                                    echo ' class="text-muted"';
                                }
                                    ?>
                            >
                                <td class="col-md-3">
                                    <?php if ($ressource->location) {
                                        echo $ressource->location;
                                    } else {
                                        echo '-';

                                    }
                                    ?>
                                </td>
                                <td class="col-md-3">
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}">{{ $ressource->name }}</a>
                                </td>
                                <td class="col-md-3">
                                    @if($ressource->subscription)
                                        <a href="{{ URL::route('organisation_modify', $ressource->subscription->organisation->id) }}">{{ $ressource->subscription->organisation->name }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="col-md-1" align="right">{{ $ressource->amount }}€</td>
                                <td class="col-md-2">
                                    <!--
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}"
                                       class="btn btn-primary btn-xs">Modifier</a>
                                       -->
                                    <a href="{{ URL::route('stats_sales_per_ressource', $ressource->id) }}"
                                       class="btn btn-primary btn-xs">Stats</a>
                                    @if($ressource->is_bookable && $ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM)
                                        <a href="{{ URL::route('ressource_status', $ressource->id) }}"
                                           class="btn btn-default btn-xs" target="_blank">iPad</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
                @endforeach
        </div>
    </div>
@stop