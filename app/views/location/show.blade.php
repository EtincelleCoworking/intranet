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
                                <td width="15%">
                                    <i class="fa fa-circle" aria-hidden="true"
                                       <?php $status = $equipment->getStatus(); ?>
                                       @if($status == 'good')
                                       style="color: green"
                                       @elseif($status == 'warning')
                                       style="color: orange"
                                       @else
                                       style="color: red"
                                            @endif
                                    ></i>
                                    @if($equipment->is_critical)
                                        {{$equipment->name}}
                                    @else
                                        <i>{{$equipment->name}}</i>
                                    @endif

                                    @if($equipment->description)
                                        <br/>
                                        <small>{{$equipment->description}}</small>
                                    @endif
                                </td>
                                <td width="10%" style="text-wrap: none">
                                    <i class="fa fa-question-circle"
                                       @if($equipment->notify_frequency)
                                       title="Mise à jour : {{$equipment->frequencyFmt()}} / Alerte : {{$equipment->notifyFrequencyFmt()}}"
                                       @else
                                       title="Mise à jour : {{$equipment->frequencyFmt()}}"
                                            @endif
                                    ></i>
                                    <small>{{$equipment->ip}}</small>

                                </td>
                                <td width="15%">
                                    @if($equipment->last_seen_at)
                                        {{$equipment->lastSeenAgo()}}
                                    @else
                                        -
                                    @endif

                                </td>
                                <td width="50%">
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