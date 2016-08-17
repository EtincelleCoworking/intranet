@extends('layouts.master')

@section('meta_title')
    Démographie des membres
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Démographie des membres</h2>
        </div>

    </div>
@stop

@section('content')

    <style type="text/css">
        .progress{margin-bottom: 0;}
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Genre des membres</h5>
                </div>
                <div class="ibox-content">
                    <table class="table">
                        <tr>
                            <td width="20%">Homme</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                         aria-valuemax="100" style="min-width: 2em; width: {{$gender['M']}}%;">
                                        {{$gender['M']}}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Femme</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                         aria-valuemax="100" style="min-width: 2em; width: {{$gender['F']}}%;">
                                        {{$gender['F']}}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Age des membres (Moyenne: {{$average}})</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-condensed">
                        <tr>
                            <th class="text-right">
                                Homme
                            </th>
                            <th class="text-center">Age</th>
                            <th>
                                Femme
                            </th>
                        </tr>
                        @foreach ($age as $a => $data)
                            <tr>
                                <td width="45%">
                                    @if($data['M']['value'])
                                        <div class="progress">
                                            <div class="progress-bar pull-right" role="progressbar" aria-valuenow="0"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"
                                                 style="min-width: 2em; width: {{$data['M']['percent']}}%;">
                                                {{$data['M']['value']}}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td align="center" width="10%">{{$a}}</td>
                                <td width="45%">
                                    @if($data['F']['value'])
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="0"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"
                                                 style="min-width: 2em; width: {{$data['F']['percent']}}%;">
                                                {{$data['F']['value']}}
                                            </div>
                                        </div>
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




