@extends('layouts.master')

@section('meta_title')
    {{$booking_item->booking->title}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                <div class="pull-right">
                    @if($booking_item->booking->is_private)
                        <span class="fa fa-2x fa-lock" id="meeting-unlocker"></span>
                    @else
                        <span class="fa fa-2x fa-unlock" id="meeting-unlocker"></span>
                    @endif
                </div>
                {{$booking_item->booking->title}}
            </h2>
        </div>
        <div class="col-sm-4">
            <h3>
                <i class="fa fa-user"></i>
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ URL::route('user_modify', $booking_item->booking->user->id) }}">{{ $booking_item->booking->user->fullname }}</a>
                    @if($booking_item->booking->user->phone)
                        <br/><i class="fa fa-phone"></i> {{ $booking_item->booking->user->phoneFmt}}
                    @endif

                @else
                    <a href="{{ URL::route('user_profile', $booking_item->booking->user->id) }}">{{ $booking_item->booking->user->fullname }}</a>
                @endif

            </h3>
        </div>
        <div class="col-sm-4">
            <h3>
                <i class="fa fa-calendar"></i>
                {{ date('d/m/Y', strtotime($booking_item->start_at)) }}
                <i class="fa fa-clock-o"></i>
                {{ date('H:i', strtotime($booking_item->start_at)) }} -
                {{ date('H:i', strtotime($booking_item->start_at) + 60 * $booking_item->duration) }}
            </h3>
        </div>
        <div class="col-sm-4">
            <h3>
                <i class="fa fa-location-arrow"></i>
                <div class="label" style="{{$booking_item->ressource->labelCss}}">
                    <label for="filter_ressource_{{$booking_item->ressource->id}}"
                           style="font-weight: 600;">{{$booking_item->ressource->name}}</label>
                </div>
            </h3>
        </div>


    </div>
@stop

@section('content')

    <div class="row">
        @if (Auth::user()->isSuperAdmin())
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-content">
                        {{$booking_item->booking->content}}
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                            <th colspan="2">WIFI
                                @if(!empty($booking_item->booking->wifi_login))
                                    <a href="{{ URL::route('booking_wifi_pdf', $booking_item->id) }}"
                                       class="btn btn-primary bt-xs pull-right" target="_blank">PDF</a>
                                @endif
                            </th>
                            </thead>
                            <tbody>
                            <tr>
                                <th width="50%">Identifiant</th>
                                <td>
                                    @if(empty($booking_item->booking->wifi_login))
                                        <a href="{{ URL::route('booking_generate_voucher', $booking_item->id) }}"
                                           class="btn btn-primary btn-xs">Générer</a>
                                    @else
                                        {{$booking_item->booking->wifi_login}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Mot de passe</th>
                                <td>{{$booking_item->booking->wifi_password}}</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        @else
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        {{$booking_item->booking->content}}
                    </div>
                </div>
            </div>
        @endif


    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="pull-right">
                        <a href="{{ URL::route('api_booking_register',$booking_item->id) }}" class="btn btn-success"
                           id="meeting-view-members-register">Inscription</a>
                        <a href="{{ URL::route('api_booking_unregister',$booking_item->id) }}" class="btn btn-success"
                           id="meeting-view-members-unregister">Désincription</a>
                    </div>
                    <h3>
                        Participants
                        <span class="badge">{{count($booking_item->members)}}</span>
                    </h3>

                </div>
                <div class="ibox-content" id="meeting-view-members">
                    <div class="row">
                        <div class="col-lg-12" id="meeting-view-members-list">
                            @foreach($booking_item->members as $member)
                                <span id="meeting-view-members-member-{{$member->id }}">
                                <a href="{{$member->profile_url }}"><img alt="{{$member->fullname }}"
                                                                         title="{{$member->fullname }}"
                                                                         class="img-circle img-responsive"
                                                                         src="{{$member->avatar_url }}"/></a></span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        @if($booking_item->isMember(Auth::id()))
        $('#meeting-view-members-register').hide();
        @else
        $('#meeting-view-members-unregister').hide();
        @endif
    </script>
@stop







