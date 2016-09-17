@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Suivi de la facturation des réservations</h2>
        </div>
        <div class="col-sm-2">
            <div class="title-action">
                <a href="#" class="btn btn-primary" id="meeting-add">Nouvelle réservation</a>
            </div>
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
                        <table class="table">
                            <thead>

                            <tr>
                                @if (Auth::user()->isSuperAdmin())
                                    <th>Utilisateur</th>
                                @endif
                                <th>Vue d'ensemble</th>
                                <th>Effectué</th>
                                <th>Réservé</th>
                                <th>Crédit</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    @if (Auth::user()->isSuperAdmin())
                                        <td>
                                            <a href="{{ route('user_modify', $item->booking->user->id) }}">{{ $item->booking->user->fullname }}</a>
                                            <a href="?filtre_submitted=1&filtre_user_id={{ $item->booking->user->id }}"><i
                                                        class="fa fa-filter"></i></a>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="progress">
                                            @if($item->quantity_done)
                                                <div class="progress-bar progress-bar-success"
                                                     style="width: {{$item->quantity_done_percent}}%">
                                                </div>
                                            @endif
                                            @if($item->quantity_pending)
                                                <div class="progress-bar progress-bar-warning progress-bar-striped"
                                                     style="width: {{$item->quantity_done_percent}}%">
                                                </div>
                                            @endif
                                            @if($item->quantity_remaining)
                                                <div class="progress-bar progress-bar-danger"
                                                     style="width: {{$item->quantity_done_percent}}%">
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{$item->quantity_done}}</td>
                                    <td>{{$item->quantity_pending}}</td>
                                    <td>
                                        @if($item->quantity_remaining<0)
                                            <span class="text-danger">{{$item->quantity_remaining}}</span>
                                        @else
                                            {{$item->quantity_remaining}}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-xs btn-primary">Détails</a>
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



