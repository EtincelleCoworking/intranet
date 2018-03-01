@extends('layouts.master')

@section('meta_title')
    {{$ressource->location}} - {{$ressource->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                {{$ressource->location}} - {{$ressource->name}}
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">


                            @if(count($items) > 0)
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Période</th>
                                        <th>Taux de remplissage</th>
                                        <th>Temps vendu</th>
                                        <th>CA</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($items as $data)
                                        <tr>
                                            <td>{{ $data->occurs_at }}</td>
                                            <td>
                                                <div class="progress">
                                                    <div style="width: {{ number_format($data->busy_rate, 0, ',', '.') }}%"
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
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ number_format($data->sold_hours, 0, ',', '.') }} heures
                                                ({{ number_format($data->sold_hours / 7, 2, ',', '.') }} jours
                                                / {{ number_format($data->working_days, 0, ',', '.') }} travaillés)
                                            </td>
                                            <td style="text-align:right">{{ number_format($data->amount, 0, ',', '.') }}
                                                € HT
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            @else
                                <p>Aucune vente</p>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">

                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Top Clients ({{date('m/Y', strtotime($top_customers_from))}} - {{date('m/Y', strtotime($top_customers_to))}})</th>
                                    <th>Montant</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 0; ?>
                                @foreach ($top_customers as $name => $data)
                                    <tr>
                                        <td>{{ ++$index }}</td>
                                        <td>{{$name}}</td>
                                        <td>{{ number_format($data['amount'], 0, ',', '.') }}€ HT</td>
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




