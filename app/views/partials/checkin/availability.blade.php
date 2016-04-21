<?php

$items = Cache::get(CheckinController::CACHE_KEY_AVAILABILITY, array());
if (count($items) == 0) {

    $results = DB::select(DB::raw('SELECT locations.id, locations.coworking_capacity, concat(cities.name, " > ", IF(locations.name IS NULL, "", locations.name)) as name
FROM locations
JOIN cities on locations.city_id = cities.id
WHERE locations.enabled = 1
ORDER BY cities.name ASC, locations.name ASC'));
    $items = array();
    foreach ($results as $result) {
        $items[$result->id] = array(
                'name' => trim($result->name, ' > '),
                'members' => array(),
                'capacity' => $result->coworking_capacity
        );
    }

    $results = DB::select(DB::raw('SELECT past_times.location_id, past_times.user_id
FROM past_times
WHERE past_times.date_past = CURDATE()
  AND ressource_id = ' . Ressource::TYPE_COWORKING . '
  AND ((time_end IS NULL) OR (time_end > NOW()))'));
    $users = array();
    foreach ($results as $result) {
        $items[$result->location_id]['members'][$result->user_id] = false;
        $users[] = $result->user_id;
    }

    foreach (User::whereIn('id', $users)->get() as $user) {
        foreach ($items as $location_id => $subItems) {
            if (isset($items[$location_id]['members'][$user->id])) {
                $items[$location_id]['members'][$user->id] = $user;
            }
        }
    }


    Cache::put(CheckinController::CACHE_KEY_AVAILABILITY, $items, 60);
}

?>
<?php unset($items['']);  ?>
@if(count($items) > 0)

    <div class="ibox" id="checkin-availability">
        <div class="ibox-title">
            <h5>Membres présents</h5>
        </div>
        <div class="ibox-content">
            <div class="media-body">
                <div>
                    @foreach($items as $index => $item)
                        <div>
                            <span>{{ $item['name'] }}</span>
                            <small class="pull-right">{{ count($item['members']) }} / {{ $item['capacity'] }}</small>
                        </div>
                        <?php $ratio = min(100, 100 * count($item['members']) / $item['capacity']); ?>
                        <div>
                            @foreach($item['members'] as $user)
                                <a href="{{URL::route('user_profile', $user->id)}}">
                                    {{$user->avatarTag}}
                                </a>
                            @endforeach
                        </div>
                        <div class="progress progress-small">
                            <div class="progress-bar @if($ratio > 80) progress-bar-danger @endif"
                                 style="width: {{ $ratio }}%;"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif