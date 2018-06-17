@extends('layouts.master')

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>TVA</h2>
        </div>
        <div class="col-sm-8">

        </div>
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Totaux trimestriels</h5>

                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th>Période</th>
                                    <th>Solde</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($overview as $period => $value)
                                    <tr>
                                        <th>{{ $period }}</th>
                                        <td align="right">
                                            @if ($value < 0)
                                                <span style="color: red">{{ sprintf('%0.2f', $value) }}€</span>
                                            @else
                                                <span style="color: green">{{ sprintf('%0.2f', $value) }}€</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Détails mensuels</h5>

                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th rowspan="2">Période</th>
                                    <th colspan="{{max(count($paid_rates), 1)}}">TVA Décaissée</th>
                                    <th colspan="{{max(count($received_rates), 1)}}">TVA Encaissée</th>
                                    <th rowspan="2">Solde</th>
                                </tr>
                                <tr>
                                    @foreach ($paid_rates as $rate)
                                        <th align="right">{{$rate}}%</th>
                                    @endforeach

                                    @forelse ($received_rates as $rate)
                                        <th align="right">{{$rate}}%</th>
                                    @empty
                                        <td align="right">-</td>
                                    @endforelse
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($sum as $period => $value)
                                    <tr>
                                        <th>{{ $period }}</th>
                                        @foreach ($paid_rates as $rate)
                                            <td align="right">{{ isset($paid[$period][$rate])?sprintf('%0.2f€', $paid[$period][$rate]):'-'}}</td>
                                        @endforeach
                                        @forelse ($received_rates as $rate)
                                            <td align="right">{{ isset($received[$period][$rate])?sprintf('%0.2f€', $received[$period][$rate]):'-'}}</td>
                                        @empty
                                            <td align="right">-</td>
                                        @endforelse
                                        <td align="right">
                                            @if ($value < 0)
                                                <span style="color: red">{{ sprintf('%0.2f', $value) }}€</span>
                                            @else
                                                <span style="color: green">{{ sprintf('%0.2f', $value) }}€</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
