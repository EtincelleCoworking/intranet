<?php

$cacheKey = 'ressource_booking_status';
//Cache::forget($cacheKey);

// TODO améliorer le cache
// se brancher sur les events add/edit/delete des booking
// purger si changement sur un event today
// mettre en cache jusqu'à demain 00:00

// Ajouter au survol la possibilité de
// - Détails
// - Réserver (pré-initialise la salle)


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
        MIN(next_booking_item.start_at) AS start_at,
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
'));

    foreach ($results as $item) {
        $delay = round((strtotime($item->start_at) - time()) / 60);
        $rooms[$item->id]['next_event'] = array(
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'user_id' => $item->user_id,
                'is_private' => $item->is_private,
                'duration' => $item->duration,
            //'delay' => durationToHumanShort($delay)
        );
        if ($delay < 30) {
            $rooms[$item->id]['next_event']['duration_kind'] = 'label-warning';
        } else {
            $rooms[$item->id]['next_event']['duration_kind'] = 'label-primary';
        }
    }

    $results = DB::select(DB::raw('
SELECT
    ressources.id,
    MIN(current_booking_item.start_at) AS start_at,
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

@if(count($rooms) > 1)
    <div class="ibox">
        <div class="ibox-title">
            <h5>Besoin d'un espace de réunion?</h5>
        </div>
        <div class="ibox-content">
            <div class="media-body">
                <a href="{{ URL::route('booking') }}">
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
                                <span style="color: #676a6c">{{ $room['name'] }}
                                    <br/>
                                    @if($room['current_event'])
                                        <small>
                                            <?php
                                            printf('Occupé jusqu\'à %s', date('H:i', strtotime($room['current_event']['end_at'])));
                                            if($room['next_event']){
                                                printf(', occupé à %s', date('H:i', strtotime($room['next_event']['start_at'])));
                                            }
                                            ?>
                                        </small>
                                    @else
                                        @if($room['next_event'])
                                            <small>
                                                Occupé de {{ date('H:i', strtotime($room['next_event']['start_at'])) }}
                                                à {{date('H:i', strtotime($room['next_event']['end_at']))}}
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                Pas de réservation
                                            </small>
                                        @endif
                                    @endif
                                </span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </a>
            </div>
        </div>
    </div>
@endif