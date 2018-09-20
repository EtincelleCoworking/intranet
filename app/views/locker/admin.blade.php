@extends('layouts.master')

@section('meta_title')
    Gestion des casiers pour {{$location->fullname}}
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Gestion des casiers pour {{$location->fullname}}
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @foreach($cabinets as $cabinet)
                <div class="ibox">
                    <div class="ibox-title">
                        <a href="{{URL::route('locker_admin_pdf', $cabinet->location_id)}}"
                           class="btn btn-primary pull-right" tar>PDF</a>
                        <h3>{{$cabinet->name}}</h3>
                        <p>{{$cabinet->description}}</p>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-striped table-hover">
                            <thead>
                            <th>Nom</th>
                            <th>Utilisateur</th>
                            <th>Depuis le</th>
                            <th>Actions</th>
                            </thead>
                            @foreach($cabinet->lockers as $locker)
                                <tr>
                                    <td class="col-md-2">
                                        {{$locker->name}}
                                    </td>
                                    @if($locker->current_usage)
                                        <td class="col-md-4">
                                            <a href="{{ URL::route('user_profile', $locker->current_usage->user->id) }}">{{ $locker->current_usage->user->fullname }}</a>
                                        </td>
                                        <td class="col-md-4">
                                            {{date('d/m/Y H:i', strtotime($locker->current_usage->taken_at))}}
                                        </td>
                                    @else
                                        <td class="col-md-4">
                                            <i> -- Disponible --</i>
                                        </td>
                                        <td class="col-md-4">-</td>
                                    @endif
                                    <td class="col-md-2">
                                            <a href="{{URL::route('locker_history', $locker->id)}}"
                                               class="btn btn-default btn-xs">Historique</a>

                                        @if($locker->current_usage)
                                            <a href="{{URL::route('locker_release', $locker->id)}}"
                                               class="btn btn-danger btn-xs">Libérer</a>
                                        @else
                                            <a href="{{URL::route('locker_toggle', array('id' => $locker->id, 'secret' => $locker->secret))}}"
                                               class="btn btn-primary btn-xs">Toggle</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach

        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {


        });
    </script>
@stop