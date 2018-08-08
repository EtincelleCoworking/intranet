@extends('layouts.master')

@section('meta_title')
    Coworking - {{$city->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Coworking - {{$city->name}}</h2>
        </div>

    </div>
@stop

@section('content')

    <style type="text/css">
        @foreach($colors as $index => $color)
            .percent<?php echo 10*$index; ?>                  {
            background-color: #{{$color}}






        }

        @endforeach

        table#stats-coworking td, table#stats-coworking th {
            font-size: 7pt;
        }

        table#stats-coworking thead {
            display: block;
        }

        table#stats-coworking tbody {
            display: block;
            height: 30em; /* 5 times the equivalent of a text "size". */
            overflow-y: scroll;
        }

        table#stats-coworking thead tr th:nth-child(1),
        table#stats-coworking tbody tr:first-child td:nth-child(1) { /* column 1 ! */
            width: 10em;
        }

        table#stats-coworking thead tr th:nth-child(2),
        table#stats-coworking thead tr th,
        table#stats-coworking tbody tr td {
            width: 5em;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Filtre</h5>
                </div>
                <div class="ibox-content">

                    {{ Form::open(array('route' => array('stats_coworking'), 'method' => 'GET')) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    <div class="row">

                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_start', date('d/m/Y', strtotime($start_at)), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_end', date('d/m/Y', strtotime($end_at)), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-3 input-group-sm">
                            {{ Form::checkbox('filtre_combined', true, $combined) }}
                            Combiné
                        </div>
                        <div class="col-md-3">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>





    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5> Taux de remplissage global: {{sprintf('%0.2f', $overall)}}%</h5>
                </div>
                <div class="ibox-content">
                    <table class="table" id="stats-coworking">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <?php
                            for ($i = $min_time; $i < 24; $i++) {
                                printf('<th>%02d:00</th>', $i);
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $date => $data)
                            <tr>
                                <td>{{$date}}</td>
                                <?php
                                for ($i = $min_time; $i < 24; $i++) {
                                    $time = sprintf('%02d:00', $i);
                                    if (isset($data[$time])) {
                                        $d = $data[$time];
                                        //var_dump($d);
                                        if (is_array($d['percent'])) {
                                            $d['percent_step'] = round((array_sum($d['percent']) / count($d['percent'])) / 10) * 10;
                                            $d['percent'] = round(array_sum($d['percent']) / count($d['percent']));
                                        }
                                        if (is_array($d['count'])) {
                                            $d['count'] = array_sum($d['count']) / count($d['count']);
                                        }
                                    } else {
                                        $d = array('count' => 0, 'percent' => 0, 'percent_step' => 0);
                                    }

                                    printf('<td class="percent%d" title="Taux de remplissage: %d%%">%d</td>', $d['percent_step'], $d['percent'], $d['count']);
                                }
                                ?>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <p class="text-muted">Scrollez dans le tableau pour voir tous les résultats</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Légende</h5>
                        </div>
                        <div class="ibox-content">
                            <table class="table">
                                <tr>
                                    <?php
                                    for ($i = 0; $i < 11; $i++) {
                                        printf('<td class="percent%1$02d">%1$d%%</td>', 10 * $i);
                                    }
                                    ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop



@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
        });
    </script>
@stop




