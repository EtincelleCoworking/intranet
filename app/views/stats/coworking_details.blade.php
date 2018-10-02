@extends('layouts.master')

@section('meta_title')
    Détail coworkers - {{$city->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Détail coworkers - {{$city->name}}</h2>
            <p>
                @foreach(City::join('locations', 'locations.city_id', '=','cities.id')->select('cities.*')->distinct()->orderBy('cities.name', 'ASC')->get() as $c)
                    <a href="{{URL::route('stats_coworking_details', array('city_id' => $c->id))}}"
                       class="btn btn-xs
                            @if($c->id == $city->id)
                               btn-primary
@else
                               btn-default
@endif
                               ">{{$c->name}}</a>
                @endforeach
            </p>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        @if(count($users) > 0)
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-sm-1">#</th>
                                    <th class="col-sm-7">Coworker</th>
                                    <th class="col-sm-2">Première visite</th>
                                    <th class="col-sm-2">Dernière visite</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $index => $user)

                                    <tr
                                            @if($user->last_seen_at < date('Y-m-d', strtotime('-3 month')))
                                            class="text-danger"
                                            @elseif($user->last_seen_at < date('Y-m-d', strtotime('-1 month')))
                                            class="text-warning"
                                            @endif
                                    >
                                        <td>{{$index + 1}}</td>
                                        <td>
                                            <a href="{{ URL::route('user_modify', $user->id) }}">{{$user->firstname}} {{$user->lastname}}</a>
                                        </td>
                                        <td>{{date('d/m/Y', strtotime($user->coworking_started_at))}}</td>
                                        <td>
                                            @if(substr($user->coworking_started_at, 0, 10) == substr($user->last_seen_at, 0, 10) )
                                                -
                                            @else
                                                {{date('d/m/Y', strtotime($user->last_seen_at))}}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                        @else
                            <p>Aucun coworker</p>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
@stop




