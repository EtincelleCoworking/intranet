@extends('layouts.master')

@section('meta_title')
    En attente de facturation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>En attente de facturation - Coworking</h2>
        </div>
        <div class="col-sm-4">
            <div class="title-action">
            </div>
        </div>
    </div>
@stop

@section('content')

    @if(count($data)==0)
        <p>Aucun élément.</p>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">

                        <div class="tabs-container">
                            <ul class="nav nav-tabs">
                                {{--*/ $index = 0 /*--}}
                                @foreach($locations as $location_id => $location_name)
                                    <li{{ $index?'':' class="active"' }}>
                                        <a data-toggle="tab" href="#tab-{{$location_id}}"
                                           id="tab{{$location_id}}action">{{$location_name}}</a>
                                    </li>
                                    {{--*/ $index++ /*--}}
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                {{--*/ $index = 0 /*--}}
                                @foreach($locations as $location_id => $location_name)
                                    <div id="tab-{{$location_id}}" class="tab-pane{{ $index?'':' active' }}">
                                        <div class="panel-body">
                                            <div class="row">
                                                <p></p>
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>Membre</th>
                                                        @foreach($periods as $month => $mock)
                                                            <th>{{date('m/Y', strtotime($month))}}</th>
                                                        @endforeach
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($data[$location_id] as $user_id => $item)
                                                        <tr>
                                                            <td>
                                                                <a href="{{ URL::route('user_modify', $user_id) }}">{{ $users[$user_id]['name'] }}</a>
                                                                <a href="{{ URL::route('pasttime_list') }}?filtre_submitted=1&filtre_toinvoice=1&filtre_start={{date('d/m/Y', Config::get('etincelle.activity_started'))}}&filtre_user_id={{ $user_id }}"><i
                                                                            class="fa fa-filter"></i></a>

                                                            </td>
                                                            @foreach($periods as $month => $mock)
                                                                <td>
                                                                    <?php
                                                                    if (isset($item[$month])) {
                                                                        echo durationToHuman($item[$month]);
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                    ?>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    {{--*/ $index++ /*--}}
                                @endforeach
                            </div>


                        </div>

                    </div>
                </div>
            </div>
            @endif
            @stop

            @section('javascript')
                <script type="text/javascript">
                    $().ready(function () {

                    });
                </script>
@stop
