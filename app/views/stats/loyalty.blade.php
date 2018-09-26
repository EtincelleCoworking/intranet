@extends('layouts.master')

@section('meta_title')
    Fidélité Coworking - {{$city->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Fidélité Coworking - {{$city->name}}</h2>
            <p>
                @foreach(City::join('locations', 'locations.city_id', '=','cities.id')->select('cities.*')->distinct()->orderBy('cities.name', 'ASC')->get() as $c)
                    <a href="{{URL::route('stats_loyalty', $c->id)}}"
                       class="btn btn-xs
                            @if($c->id == $city->id)
                               btn-primary
                               @else
                               btn-default
                               @endif
                               ">{{$c->name}}</a>
                @endforeach
            </p>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="col-md-2">Mois</th>
                            <th class="col-md-2">Coworkers</th>
                            <th class="col-md-2">Revenus<br />
                                <small>dans les 3 mois</small>
                            </th>
                            <th class="col-md-6">Ratio</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($result as $date => $data)
                            <tr>
                                <td>{{date('m/Y',strtotime($date))}}</td>
                                <td>{{$data['total']}}</td>
                                <td>{{$data['went_back']}}</td>
                                <td>
                                    <div class="progress" style="margin-bottom: 5px">
                                        <div style="width: {{round($data['ratio'], 0)}}%" aria-valuemax="100"
                                             aria-valuemin="0" aria-valuenow="{{round($data['ratio'], 0)}}"
                                             role="progressbar" class="progress-bar">
                                            {{round($data['ratio'], 0)}}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


@stop



@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
        });
    </script>
@stop




