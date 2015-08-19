<?php

$events = BookingItem::where('start_at', '>', date('Y-m-d H:i:s'))
        ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
        ->where('booking.is_private', '=', false)
        ->where('start_at', '<', date('Y-m-d', strtotime('+2 weeks')))
        ->with('booking', 'ressource')
        ->orderBy('start_at', 'ASC')
//        ->take(3)
        ->get();

?>

<div class="ibox">
    <div class="ibox-title">
        <h5>Prochains rendez-vous</h5>
    </div>
    <div class="ibox-content">
        <div class="feed-activity-list">
            @foreach($events as $booking_item)
                <div class="feed-element">
                    <div class="media-body ">
                        <small class="pull-right text-navy">{{date('d/m H:i', strtotime($booking_item->start_at))}}</small>
                        <strong>
                            <a href="{{route('booking')}}">{{ $booking_item->booking->title }}</a>
                        </strong><br/>
                        {{--<small class="text-muted">X participants</small>--}}
                        {{--<div class="actions pull-right">--}}
                            {{--<a class="btn btn-xs btn-white"><i class="fa fa-thumbs-up"></i> Participer </a>--}}
                        {{--</div>--}}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>