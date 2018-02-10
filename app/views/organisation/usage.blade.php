@extends('layouts.master')

@section('meta_title')
    Activité de la société {{$organisation->name}} sur la période {{$period}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Activité de la société {{$organisation->name}} sur la période {{$period}}</h2>
            <?php
            for ($i = 11; $i >= 0; $i--) {
                $when = strtotime(sprintf('-%d month', $i));
                $target_period = date('Y-m', $when);
                $activeStr = ($target_period == $period) ? ' btn-primary' : ' btn-default';
                printf('<a href="%s" class="btn btn-xs%s">%s</a>'."\n", URL::route('organisation_usage', array('id' => $organisation->id, 'period' => $target_period)), $activeStr, date('m/Y', $when));
            }
            ?>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        @foreach($users as $user_id => $user)
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>{{$user->fullname}}
                        </h5>
                        <div class="pull-right">
                            @if(!empty($devices[$user_id]))
                                @foreach($devices[$user_id] as $data)
                                    <span class="label
@if($data['active'])
                                            label-primary
@endif
                                            ">{{$data['mac']}}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-6">
                                Coworking:
                                @if(!empty($coworking[$user_id]))

                                    @if($coworking[$user_id]['hours']||$coworking[$user_id]['minutes'])
                                        @if ($coworking[$user_id]['hours'])
                                            {{ $coworking[$user_id]['hours'] }} h
                                        @endif
                                        @if ($coworking[$user_id]['minutes'])
                                            {{ $coworking[$user_id]['minutes'] }} min
                                        @endif
                                    @else
                                        0 h
                                    @endif
                                @else
                                    0 h
                                @endif
                            </div>
                            <div class="col-lg-6">
                                @if(!empty($rooms[$user_id]))
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Location de salle</th>
                                            <th>Durée</th>
                                        </tr>
                                        </thead>
                                        @foreach($rooms[$user_id] as $room_name => $room_usage)
                                            <tr>
                                                <td>{{$room_name}}</td>
                                                <td>
                                                    @if($room_usage['hours']||$room_usage['minutes'])
                                                        @if ($room_usage['hours'])
                                                            {{ $room_usage['hours'] }} h
                                                        @endif
                                                        @if ($room_usage['minutes'])
                                                            {{ $room_usage['minutes'] }} min
                                                        @endif
                                                    @else
                                                        0 h
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop
