@extends('layouts.master')

@section('meta_title')
    {{$location->fullName}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-ls-12">
            <h2>{{$location->fullName}}</h2>

        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    <table class="table table-bordered">
                        <tr>
                            <th>Equipement</th>
                            <th>IP</th>
                            <th>Mis à jour</th>
                        </tr>
                        @foreach($equipments as $equipment)
                            <tr>
                                <td>
                                    <i class="fa fa-circle" aria-hidden="true"
                                       @if($equipment->isUp())
                                       style="color: green"
                                       @else
                                       style="color: red"
                                            @endif
                                    ></i>
                                    {{$equipment->name}}
                                </td>
                                <td>{{$equipment->ip}}</td>
                                <td>
                                    @if($equipment->last_seen_at)
                                        {{date('d/m/Y H:i', strtotime($equipment->last_seen_at))}}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>

                </div>

            </div>
        </div>
    </div>
@stop