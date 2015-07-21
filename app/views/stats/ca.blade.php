@extends('layouts.master')

@section('meta_title')
    Statistiques
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Statistiques</h2>
        </div>

    </div>
@stop

@section('content')
    {{ HTML::script('js/plugins/chartjs/Chart.min.js') }}

    @if(count($charts) == 1)
        {{--*/ $index = 0 /*--}}
        @foreach($charts as $name => $chart)
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>{{$name}}</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <canvas id="myChart"></canvas>
                                <script type="text/javascript">

                                    var data = {
                                        labels: [@foreach($chart as $period => $value) "{{$period}}", @endforeach],
                                        datasets: [
                                            {
                                                label: "{{$name}}",
                                                fillColor: "rgba(151,187,205,0.5)",
                                                strokeColor: "rgba(151,187,205,0.8)",
                                                highlightFill: "rgba(151,187,205,0.75)",
                                                highlightStroke: "rgba(151,187,205,1)",
                                                data: [@foreach($chart as $period => $value) "{{ $value }}", @endforeach]
                                            }
                                        ]
                                    };
                                    var ctx = document.getElementById("myChart").getContext("2d");
                                    var barOptions = {
                                        scaleBeginAtZero: true,
                                        scaleShowGridLines: true,
                                        scaleGridLineColor: "rgba(0,0,0,.05)",
                                        scaleGridLineWidth: 1,
                                        barShowStroke: true,
                                        barStrokeWidth: 2,
                                        barValueSpacing: 5,
                                        barDatasetSpacing: 1,
                                        responsive: true,
                                    }
                                    var myLineChart = new Chart(ctx).Bar(data, barOptions);
                                </script>
                                {{--*/ $index++ /*--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                {{--*/ $index = 0 /*--}}
                @foreach($charts as $name => $chart)
                    <li{{ $index?'':' class="active"' }}>
                        <a data-toggle="tab" href="#tab-{{$index}}" id="tab{{$index}}action">{{$name}}</a>
                    </li>
                    {{--*/ $index++ /*--}}
                @endforeach
            </ul>
            <div class="tab-content">
                {{--*/ $index = 0 /*--}}
                @foreach($charts as $name => $chart)
                    <div id="tab-{{$index}}" class="tab-pane{{ $index?'':' active' }}">
                        <div class="panel-body">
                            <canvas id="myChart{{$index}}"></canvas>
                        </div>
                    </div>
                    <script type="text/javascript">
                        function refreshTab{{$index}}() {
                            var data{{$index}}       = {
                                labels: [@foreach($chart as $period => $value) "{{$period}}", @endforeach],
                                datasets: [
                                    {
                                        label: "{{$name}}",
                                        fillColor: "rgba(151,187,205,0.5)",
                                        strokeColor: "rgba(151,187,205,0.8)",
                                        highlightFill: "rgba(151,187,205,0.75)",
                                        highlightStroke: "rgba(151,187,205,1)",
                                        data: [@foreach($chart as $period => $value) "{{ $value }}", @endforeach]
                                    }
                                ]
                            };


                            var myLineChart{{$index}} = null;


                            var ctx = document.getElementById("myChart{{$index}}").getContext("2d");

                            var barOptions = {
                                scaleBeginAtZero: true,
                                scaleShowGridLines: true,
                                scaleGridLineColor: "rgba(0,0,0,.05)",
                                scaleGridLineWidth: 1,
                                barShowStroke: true,
                                barStrokeWidth: 2,
                                barValueSpacing: 5,
                                barDatasetSpacing: 1,
                                responsive: true
                            }
                            if (myLineChart{{$index}}      != null) {
                                myLineChart{{$index}}.destroy();
                            }
                            myLineChart{{$index}} = new Chart(ctx).Bar(data{{$index}}, barOptions);
                        }
                    </script>
                    {{--*/ $index++ /*--}}
                @endforeach
            </div>
        </div>
    @endif


@stop


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            {{--*/ $index = 0 /*--}}
            @foreach($charts as $name => $chart)
                $('a[href="#tab-{{$index}}"]').on('shown.bs.tab', function (e) {
                        refreshTab{{$index}}();
                    });
            {{--*/ $index++ /*--}}
            @endforeach

            refreshTab0();
        });
    </script>
@stop
