@extends('layouts.master')

@section('meta_title')
    Statistiques &gt; Abonnements
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Statistiques &gt; Abonnements</h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Répartition globale</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered table-striped table-hover">
                        <tbody>

                        @foreach ($ratio_all as $name => $ratio_data)
                            <tr>
                                <th>{{$name}}</th>
                                <td>{{ $ratio_data['count'] }}</td>
                                <td>{{ number_format($ratio_data['ratio'], 1, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($ratio_spaces as $location => $space_datas)
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{$location}}</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered table-striped table-hover">
                            <tbody>
                            @foreach ($space_datas as $name => $ratio_data)
                                <tr>
                                    <th>{{$name}}</th>
                                    <td>{{ $ratio_data['count'] }}</td>
                                    <td>{{ number_format($ratio_data['ratio'], 1, ',', '.') }}%</td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Abonnements à venir</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Mois</th>
                            @foreach ($datas as $period => $amount)
                                <td>{{$period}}</td>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <th>Montant HT</th>
                        @foreach ($datas as $period => $amount)
                            <td>
                                {{ number_format($amount, 0, ',', '.') }}€
                            </td>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


@stop
