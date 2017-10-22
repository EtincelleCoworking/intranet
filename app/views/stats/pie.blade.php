@extends('layouts.master')

@section('meta_title')
    Statistiques &gt; Répartition de l'activité
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Statistiques &gt; Répartition de l'activité
                @if($period)
                    - {{ date('m/Y', $period) }}
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')
    {{ HTML::script('js/plugins/chartJs/Chart.min.js') }}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Répartition de l'activité entre les différentes catégories</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <canvas id="doughnutChart" height="140"></canvas>
                            <script type="text/javascript">
                                var doughnutData = [
                                        @foreach($data as $label => $value)
                                    {
                                        value: {{$value['amount']}},
                                        color: '{{$value['color']}}',
                                        highlight: "#1ab394",
                                        label: "{{$label}}"
                                    },
                                    @endforeach
                            ];

                                var doughnutOptions = {
                                    segmentShowStroke: true,
                                    segmentStrokeColor: "#fff",
                                    segmentStrokeWidth: 2,
                                    percentageInnerCutout: 45, // This is 0 for Pie charts
//                                animationSteps: 100,
//                                animationEasing: "easeOutBounce",
//                                animateRotate: true,
//                                animateScale: false,
                                    responsive: true,
                                };


                                var ctx = document.getElementById("doughnutChart").getContext("2d");
                                var myNewChart = new Chart(ctx).Doughnut(doughnutData, doughnutOptions);


                            </script>
                        </div>
                        <div class="col-lg-6">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th align="right">Montant HT</th>
                                    <th align="right">%</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data as $label => $value)
                                    <tr>
                                        <td>
                                            <div style="width: 2em; height: 1.2em; background-color: {{$value['color']}}; border: 1px solid #999" class="pull-left">&nbsp;</div>
                                            &nbsp;{{ $label }}
                                        </td>
                                        <td align="right">{{ number_format($value['amount'], 0, ',', '.') }}€</td>
                                        <td align="right">{{ $value['ratio'] }}%</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td align="right">{{number_format($total, 0, ',', '.')}}€</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop




