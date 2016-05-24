<?php

$events = BookingItem::where('start_at', '>', date('Y-m-d H:i:s'))
        ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
        ->where('booking.is_private', '=', false)
        ->where('start_at', '<', date('Y-m-d', strtotime('+2 weeks')))
        ->with('booking', 'ressource')
        ->orderBy('start_at', 'ASC')
        ->select('booking_item.id', 'booking.title', 'booking_item.start_at')
        ->get();

?>
@if(count($events) > 0)
    <div class="ibox">
        <div class="ibox-title">
            <h5>Prochains rendez-vous</h5>
        </div>
        <div class="ibox-content">
            <div class="feed-activity-list">
                @foreach($events as $event)
                    <div class="feed-element">
                        <div class="media-body ">
                            <small class="pull-right text-navy">{{date('d/m H:i', strtotime($event->start_at))}}</small>
                            <strong>
                                <a href="{{route('booking_item_show', array('id' => $event->id))}}">{{ $event->title }}</a>
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
@endif