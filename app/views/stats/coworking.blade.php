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
            .percent<?php echo 10*$index; ?>            {
            background-color: #{{$color}}








        }

        @endforeach

        table#stats-coworking td, table#stats-coworking th {
            font-size: 7pt;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <table class="table">
                <tr>
                    <?php
                    for ($i = 0; $i < 11; $i++) {
                        printf('<td class="percent%1$02d">%1$d%%</td>', 10 * $i);
                    }
                    ?>
                </tr>
            </table>

            <a href="?combined=0" class="btn <?php echo $combined?'btn-default':'btn-primary'; ?>">Normal</a>
            <a href="?combined=1" class="btn <?php echo $combined?'btn-primary':'btn-default'; ?>">Combiné</a>


            <table class="table" id="stats-coworking">
                <tr>
                    <td>Date</td>
                    <?php
                    for ($i = $min_time; $i < 24; $i++) {
                        printf('<th>%02d:00</th>', $i);
                    }
                    ?>
                </tr>
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
            </table>
        </div>
    </div>


@stop




