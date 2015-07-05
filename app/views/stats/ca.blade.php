@extends('layouts.master')

@section('content')
    <h1>Statistiques</h1>
    {{ HTML::script('js/Chart.js') }}

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-money fa-2x"></i>
                        <span class="pull-right">
                            Total Encours Clients</span>
                    </h3>
                </div>
                <div class="panel-body">
                    <h3 align="center">{{ sprintf('%0.2f€', $pending['total']) }}</h3>
                </div>
            </div>
        </div>
    </div>


    {{--*/ $index = 0 /*--}}



    <div class="row">
    @foreach($charts as $name => $chart)
            <div class="col-lg-6">
        <div class="portlet portlet-default">
            <div class="portlet-header">
            <h4 class="portlet-title">
                {{$name}}
            </h4>
            </div>
            <div class="portlet-body">
                <canvas id="myChart{{$index}}" class="col-md-12"></canvas>
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
                    var ctx = document.getElementById("myChart{{$index}}").getContext("2d");
                    var myLineChart = new Chart(ctx).Bar(data, []);
                </script>
                {{--*/ $index++ /*--}}
            </div>

        </div>
        </div>



    @endforeach
    </div>



@stop
