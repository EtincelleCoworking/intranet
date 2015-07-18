@extends('layouts.master')

@section('content')
    <h1>Statistiques</h1>
    {{ HTML::script('js/Chart.js') }}




    {{--*/ $index = 0 /*--}}



    @foreach($charts as $name => $chart)
        <div class="col-lg-12">
            <div class="portlet portlet-default">
                <div class="portlet-header">
                    <h4 class="portlet-title">
                        {{$name}}
                    </h4>
                </div>
                <div class="portlet-body">
                    <div class="row">
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
                        var myLineChart = new Chart(ctx)
                                        .Bar(data, [])
                                        .Line(data, [])
                                ;
                    </script>
                    {{--*/ $index++ /*--}}
                    </div>
                </div>

            </div>
        </div>



    @endforeach



@stop
