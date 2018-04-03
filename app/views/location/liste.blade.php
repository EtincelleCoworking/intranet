@extends('layouts.master')

@section('meta_title')
    Liste des sites
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Liste des sites</h2>
        </div>
        <div class="col-sm-4">

        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                </div>
                <div class="ibox-content">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>IP</th>
                            <th>Coworking</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $location)
                            <tr
                                    @if (!$location->enabled)
                                    class="text-muted"
                                    @endif
                            >
                                <td class="col-md-3">{{$location->fullName}}</td>
                                <td class="col-md-3">
                                    @if(count($location->ips) == 0)
                                        <i class="fa fa-circle" style="color: red" aria-hidden="true"></i>
                                        Aucune IP enregistrée
                                    @else
                                        @foreach($location->ips as $ip)
                                            <?php $age = $ip->getAge(); ?>
                                            <i class="fa fa-circle" aria-hidden="true"
                                               @if($age < 1)
                                               style="color: green"
                                               @elseif($age < 2)
                                               style="color: orange"
                                               @else
                                               style="color: red"
                                               @endif
                                               title="Mis à jour: {{ date('d/m/Y H:i', strtotime($ip->updated_at)) }}"></i>

                                            {{$ip->name}}
                                            <br/>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="col-md-3">
                                    {{$location->coworking_capacity}}
                                </td>
                                <td class="col-md-2">
                                    <a href="{{ URL::route('location_modify', $location->id) }}"
                                       class="btn btn-primary btn-xs">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop