@extends('layouts.master')
<?php

$target_year = substr($target_period, 0, 4);
?>
@section('content')

    @if (Auth::user()->isSuperAdmin())
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.sales.monthly.component', array('target_period' => $target_period))
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.charges.monthly.component', array('target_period' => $target_period))
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.pending.component', array('target_period' => $target_period))
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.sales.yearly.component', array('target_period' => $target_period))
            </div>
        </div>
        <div class="row">

            <table class="table table-bordered">
                @foreach($datas as $location => $location_datas)
                    <tr>
                        <td>
                            {{$location}}
                        </td>
                        <td>
                            {{ number_format( $location_datas[$target_year][$target_period]['sales'], 0, ',', '.') }}€
                        </td>
                        <td>
                            {{ number_format( $location_datas[$target_year][$target_period]['cost'], 0, ',', '.') }}€
                        </td>
                        <td>
                            @if ($location_datas[$target_year][$target_period]['balance'] < 0)
                                <span style="color: red">{{ number_format( $location_datas[$target_year][$target_period]['balance'], 0, ',', '.') }}
                                    €</span>
                            @else
                                <span style="color: green">{{ number_format( $location_datas[$target_year][$target_period]['balance'], 0, ',', '.') }}
                                    €</span>
                            @endif
                        </td>
                    </tr>
                    @if(isset($ressources[$location]))
                        <tr>
                            <td colspan="4">
                                <table class="table">
                                    <?php
                                    foreach($ressources[$location] as $ressource_id => $ressource_data){
                                    $ressource_stats = Ressource::getStatForRessource($ressource_id);
                                    if(
                                    (($ressource_data['ressource_kind_id'] == RessourceKind::TYPE_MEETING_ROOM)
                                        && ($ressource_data['is_bookable'] || ($data->amount != 0)))
                                    || ($ressource_data['ressource_kind_id'] != RessourceKind::TYPE_MEETING_ROOM)){
                                    $data = array_shift($ressource_stats);
                                    while (is_object($data) && ($data->occurs_at != $target_period && (count($ressource_stats) > 0))) {
                                        $data = array_shift($ressource_stats);
                                    }
                                    //var_dump($data); exit;
                                    if (!is_object($data) || ($data->occurs_at != $target_period)) {
                                        $data = new stdClass();
                                        $data->busy_rate = 0;
                                        $data->amount = 0;
                                    }
                                    ?>
                                    <tr>
                                        <td class="col-md-6">{{ $ressource_data['name'] }}</td>
                                        <td class="col-md-3">
                                            @if($ressource_data['ressource_kind_id'] == RessourceKind::TYPE_MEETING_ROOM)
                                                <div class="progress"><div style="width: {{ number_format($data->busy_rate, 0, ',', '.') }}%"
                                                         aria-valuemax="100" aria-valuemin="0"
                                                         aria-valuenow="{{ number_format($data->busy_rate, 0, ',', '.') }}"
                                                         role="progressbar" class="progress-bar
@if($data->busy_rate > 60)
                                                            progress-bar-primary
@elseif($data->busy_rate > 30)
                                                            progress-bar-warning
@else
                                                            progress-bar-danger
@endif
                                                            ">
                                                        <span class="sr-only">{{ number_format($data->busy_rate, 0, ',', '.') }}
                                                            %</span>
                                                        {{ number_format($data->busy_rate, 0, ',', '.') }}%
                                                    </div></div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        {{--
                                        <td>
                                            {{ number_format($data->sold_hours, 0, ',', '.') }} heures
                                            ({{ number_format($data->sold_hours / 7, 2, ',', '.') }} jours
                                            / {{ number_format($data->working_days, 0, ',', '.') }} travaillés)
                                        </td>
                                        --}}
                                        <td class="col-md-3"
                                            style="text-align:right">{{ number_format($data->amount, 0, ',', '.') }}
                                            € HT
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    }
                                    ?>
                                </table>
                                @endif
                                @endforeach
                            </td>
                        </tr>

            </table>

        </div>



    @endif

@stop

@section('javascript')
@stop




