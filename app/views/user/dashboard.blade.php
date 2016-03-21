@extends('layouts.master')

@section('content')

    @if (Auth::user()->isSuperAdmin())
        <div class="row">
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                @include('partials.sales.monthly.component')
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                @include('partials.pending.component')
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                @include('partials.sales.yearly.component')
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                @include('partials.charges.component')
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 hidden-xs">
                @include('partials.checkin.status')
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8">
                @include('partials.checkin.availability')
            @include('partials.wall.component')
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-4">
            @include('partials.checkin.component')
            @if (Auth::user()->isSuperAdmin())
            @elseif (Auth::user()->role == 'member')
                @include('partials.active_subscription')
                @include('partials.member.component')
            @endif
            @include('booking::partials.ressource_booking_status')
            @include('booking::partials.upcoming_events')
            @include('partials.next_birthday.component')
        </div>
    </div>

@stop

@section('javascript')
    {{ HTML::script('js/jquery.waypoints.min.js') }}
    {{ HTML::script('js/infinite.min.js') }}
@stop




