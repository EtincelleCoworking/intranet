<?php

$cacheKey = 'ressource_booking_status';
//Cache::forget($cacheKey);

$rooms = Cache::get($cacheKey, array());
if (count($rooms) == 0) {

    $results = DB::select(DB::raw('SELECT id, name FROM ressources WHERE is_bookable = true ORDER BY order_index'));

    $rooms = array();
    foreach ($results as $item) {
        $rooms[$item->id] = array(
                'name' => $item->name,
                'current_event' => null,
                'next_event' => null
        );
    }
    $results = DB::select(DB::raw('
    SELECT
        ressources.id,
        next_booking_item.start_at,
        next_booking_item.duration,
        DATE_ADD(next_booking_item.start_at, INTERVAL next_booking_item.duration MINUTE) as end_at,
        next_booking.user_id,
        next_booking.is_private
    FROM ressources
      LEFT OUTER JOIN booking_item next_booking_item ON next_booking_item.ressource_id = ressources.id
      LEFT OUTER JOIN booking next_booking ON next_booking_item.booking_id = next_booking.id
    WHERE
      ressources.is_bookable = true
      AND next_booking_item.start_at BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d %H:%i:00") AND DATE_FORMAT(NOW(),"%Y-%m-%d 23:59:59")
    GROUP BY ressources.id
    ORDER BY next_booking_item.start_at ASC
'));

    foreach ($results as $item) {
        $duration = round((strtotime($item->start_at) - time()) / 60);
        $rooms[$item->id]['next_event'] = array(
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'user_id' => $item->user_id,
                'is_private' => $item->is_private,
                'duration' => $item->duration,
                'delay' => durationToHumanShort($duration)
        );
        if ($duration < 30) {
            $rooms[$item->id]['next_event']['duration_kind'] = 'label-warning';
        } else {
            $rooms[$item->id]['next_event']['duration_kind'] = 'label-primary';
        }
    }

    $results = DB::select(DB::raw('
SELECT
    ressources.id,
    current_booking_item.start_at,
    DATE_ADD(current_booking_item.start_at, INTERVAL current_booking_item.duration MINUTE) as end_at,
    current_booking.user_id,
    current_booking.is_private
FROM ressources
  JOIN booking_item current_booking_item ON current_booking_item.ressource_id = ressources.id
  JOIN booking current_booking ON current_booking_item.booking_id = current_booking.id
WHERE
  ressources.is_bookable = true
  AND NOW() > current_booking_item.start_at
  AND DATE_ADD(current_booking_item.start_at, INTERVAL current_booking_item.duration MINUTE) > NOW()
GROUP BY ressources.id
ORDER BY current_booking_item.start_at ASC
'));

    foreach ($results as $item) {
        $rooms[$item->id]['current_event'] = array(
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'user_id' => $item->user_id,
                'is_private' => $item->is_private
        );

    }

    Cache::put($cacheKey, $rooms, 5);
}

?>
@if(count($rooms) > 0)
    <div class="ibox">
        <div class="ibox-title">
            <h5>Besoin d'un espace de réunion?</h5>
        </div>
        <div class="ibox-content">
            <div class="media-body">
                <table class="table table-hover no-margins">
                    @foreach($rooms as $room)
                        <tr>
                            <td>
                                @if($room['current_event'])
                                    <span class="label label-danger">KO</span>
                                @else
                                    @if($room['next_event'])
                                        <span class="label {{$room['next_event']['duration_kind']}}">
                                        OK
                                    </span>
                                    @else
                                        <div class="label label-primary">OK</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $room['name'] }}
                                <br/>
                                @if($room['current_event'])
                                    <small>
                                        Occupé jusqu'à {{ date('H:i', strtotime($room['current_event']['end_at'])) }}
                                    </small>
                                    @if($room['next_event'])
                                        <small>
                                            - Prochain: {{ date('H:i', strtotime($room['next_event']['start_at'])) }}
                                        </small>
                                    @else
                                    @endif
                                @else
                                    @if($room['next_event'])
                                        <small>
                                            Prochain: {{ date('H:i', strtotime($room['next_event']['start_at'])) }}
                                            ({{durationToHuman($room['next_event']['duration'])}})
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            Pas de réservation aujourd'hui
                                        </small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endif