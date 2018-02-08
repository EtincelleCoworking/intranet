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
            <div class="ibox">
                <div class="ibox-content">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Site</th>
                            <th>Type</th>
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
                                if ($ressource->ressource_kind_id == RessourceKind::TYPE_MEETING_ROOM
                                && !$ressource->is_bookable) {
                                    echo ' class="text-muted"';
                                }
                                    ?>
                            >
                                <td>
                                    <small>
                                    <?php if ($ressource->location) {
                                        echo $ressource->location;
                                    } else {
                                        echo '-';

                                    }
                                    ?></small>
                                </td>
                                <td>
                                    <small>
                                    <?php if ($ressource->kind) {
                                        echo $ressource->kind;
                                    } else {
                                        echo '-';
                                    }
                                    ?></small>
                                </td>
                                <td>
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}">{{ $ressource->name }}</a>
                                </td>
                                <td>
                                    @if($ressource->subscription)
                                        {{$ressource->subscription->organisation->name}}
                                    @else
                                        -
                                    @endif
                                </td>
                            <!--
                                <td>{{ $ressource->is_bookable?'Oui':'Non' }}</td>
                                -->
                                <td align="right">{{ $ressource->amount }}€</td>
                            <!--
                                <td>
                                    @if ($ressource->order_index > 1)
                                <a href="{{ URL::route('ressource_order_up', $ressource->id) }}"><i
                                                    class="fa fa-caret-square-o-up"></i></a>
                                    @endif
                            {{ $ressource->order_index }}
                            @if ($ressource->order_index < $last)
                                <a href="{{ URL::route('ressource_order_down', $ressource->id) }}"><i
                                                    class="fa fa-caret-square-o-down"></i></a>
                                    @endif
                                    </td>
-->
                                <td>
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}"
                                       class="btn btn-primary btn-xs">Modifier</a>
                                    <a href="{{ URL::route('stats_sales_per_ressource', $ressource->id) }}"
                                       class="btn btn-default btn-xs">Stats</a>
                                    @if($ressource->is_bookable)
                                        <a href="{{ URL::route('ressource_status', $ressource->id) }}"
                                           class="btn btn-default btn-xs" target="_blank">Affichage</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5">{{ $ressources->links() }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop