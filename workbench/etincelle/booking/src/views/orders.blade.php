@extends('layouts.master')

@section('meta_title')
    Suivi de la consommation des pré-réservations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Suivi de la consommation des pré-réservations</h2>
        </div>
        <div class="col-sm-2">
            {{--<div class="title-action">--}}
                {{--<a href="#" class="btn btn-primary" id="meeting-add">Nouvelle réservation</a>--}}
            {{--</div>--}}
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(count($items) == 0)
                        <p>Aucun élément à afficher</p>
                    @else
                        <table class="table table-hover table-striped">
                            <thead>

                            <tr>
                                <th>Utilisateur</th>
                                <th>Facturé</th>
                                <th>Effectué</th>
                                <th>Delta</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    @if (Auth::user()->isSuperAdmin())
                                        <td>
                                            <a href="{{ route('user_modify', $item->id) }}">{{ $item->firstname }} {{ $item->lastname }}</a>
                                        </td>
                                    @endif
                                    <td>
                                        <a href="{{route('invoice_list')}}?filtre_submitted=1&filtre_start=0&filtre_end=0&filtre_user_id={{ $item->id }}" class="btn btn-xs btn-default">Détails</a>
                                        {{$item->quantity_ordered?durationToHuman($item->quantity_ordered):'-'}}
                                    </td>
                                    <td>
                                        <a href="{{route('booking_list')}}?filtre_submitted=1&filtre_start=0&filtre_end=0&filtre_user_id={{ $item->id }}" class="btn btn-xs btn-default">Détails</a>
                                        {{$item->quantity_used?durationToHuman($item->quantity_used):'-'}}
                                    </td>
                                    <td>
                                        @if($item->quantity_ordered<$item->quantity_used)
                                            <span class="text-danger">{{ durationToHuman($item->quantity_used - $item->quantity_ordered)}}</span>
                                        @else
                                            {{durationToHuman($item->quantity_used - $item->quantity_ordered) }}
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



