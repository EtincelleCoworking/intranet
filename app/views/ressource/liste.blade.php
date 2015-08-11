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
                    <a href="{{ URL::route('ressource_add') }}" class="btn btn-default">Ajouter une ressource</a>
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
                            <th>Nom</th>
                            <th>Réservable</th>
                            <th>Prix horaire HT</th>
                            <th>Ordre</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($ressources as $n => $ressource)
                            <tr>
                                <td>
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}">{{ $ressource->name }}</a>
                                </td>
                                <td>{{ $ressource->is_bookable?'Oui':'Non' }}</td>
                                <td align="right">{{ $ressource->amount }}€</td>
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
                                <td>
                                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}" class="btn btn-default btn-xs btn-outline">Modifier</a>
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