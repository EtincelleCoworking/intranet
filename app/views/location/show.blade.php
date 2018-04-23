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
                            <th>Données</th>
                        </tr>
                        @foreach($equipments as $equipment)
                            <tr>
                                <td class="col-md-2">
                                    <i class="fa fa-circle" aria-hidden="true"
                                       <?php $age = $equipment->getAge(); ?>
                                       @if($age < 2)
                                       style="color: green"
                                       @elseif($age < 5)
                                       style="color: orange"
                                       @else
                                       style="color: red"
                                            @endif
                                    ></i>
                                    {{$equipment->name}}
                                </td>
                                <td class="col-md-2">{{$equipment->ip}}</td>
                                <td class="col-md-2">
                                    @if($equipment->last_seen_at)
                                        {{date('d/m/Y H:i', strtotime($equipment->last_seen_at))}}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="col-md-6">
                                    {{$equipment->dataFmt()}}
                                </td>
                            </tr>
                        @endforeach
                    </table>

                </div>

            </div>
        </div>
    </div>
@stop