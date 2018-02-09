@extends('layouts.master')

@section('meta_title')
    Activité de la société {{$organisation->name}} sur la période {{$period}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Activité de la société {{$organisation->name}} sur la période {{$period}}</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        @foreach($users as $user_id => $user)
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>{{$user->fullname}}</h5>
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
                                Location de salles:
                                @if(!empty($rooms[$user_id]))
                                    <ul>
                                        @foreach($rooms[$user_id] as $room_name => $room_usage)
                                            <li>{{$room_name}}:
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
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
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
