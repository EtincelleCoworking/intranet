@extends('layouts.master')

@section('content')

            {{--
    @if (Auth::user()->isSuperAdmin())
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.sales.monthly.component')
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.pending.component')
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-4">
                @include('partials.sales.yearly.component')
            </div>
            <div class="col-lg-2 col-md-4 hidden-sm hidden-xs">
                @include('partials.charges.component')
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 hidden-xs">
                @include('partials.checkin.status')
            </div>
        </div>
    @endif
            --}}

    <div class="row">
        <div class="col-lg-9 col-md-8 col-sm-6 col-xs-8">
            <?php
            $items = DB::select(DB::raw('SELECT count(*) as cnt FROM past_times WHERE date_past < CURRENT_DATE() AND device_id IS NOT NULL AND confirmed IS NULL AND is_free = 0 AND invoice_id = 0 AND user_id = ' . Auth::id()));
            if ($items[0]->cnt) {
                echo '<div class="alert alert-warning" role="alert">';
                printf('<p>Vous avez actuellement %d plage horaire qui ont été détectées automatiquement et que vous n\'avez pas confirmées</p>', $items[0]->cnt);
                printf('<p><a href="%s" class="btn btn-warning">Confirmez les maintenant</a></p>', route('pasttime_list'));
                echo '</div>';
            }
            ?>
            @include('partials.wall.component')
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-4">
            {{--
            @include('partials.checkin.component')
            --}}
            @include('partials.slack')
            @include('partials.checkin.availability')
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




