@extends('layouts.master')

@section('content')
    <h1>TVA</h1>

    <div class="row">
        <div class="col-md-4">
            <table class="table table-hover table-bordered">
                <thead>
                <tr>
                    <th>Période</th>
                    <th >Solde créditeur</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($overview as $period => $value)
                    <tr>
                        <th>{{ $period }}</th>
                        <td align="right">{{ sprintf('%0.2f', $value) }}€</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-hover table-bordered">
                <thead>
                <tr>
                    <th rowspan="2">Période</th>
                    <th colspan="{{count($paid_rates)}}">TVA Décaissée</th>
                    <th colspan="{{count($received_rates)}}">TVA Encaissée</th>
                    <th rowspan="2">Solde créditeur</th>
                </tr>
                <tr>
                    @foreach ($paid_rates as $rate)
                        <th align="right">{{$rate}}%</th>
                    @endforeach
                    @foreach ($received_rates as $rate)
                        <th align="right">{{$rate}}%</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($sum as $period => $value)
                    <tr>
                        <th>{{ $period }}</th>
                        @foreach ($paid_rates as $rate)
                            <td align="right">{{ isset($paid[$period][$rate])?sprintf('%0.2f€', $paid[$period][$rate]):'-'}}</td>
                        @endforeach
                        @foreach ($received_rates as $rate)
                            <td align="right">{{ isset($received[$period][$rate])?sprintf('%0.2f€', $received[$period][$rate]):'-'}}</td>
                        @endforeach
                        <td align="right">{{ sprintf('%0.2f', $value) }}€</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
