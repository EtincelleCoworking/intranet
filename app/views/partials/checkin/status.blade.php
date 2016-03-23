<?php

$items = Cache::get(CheckinController::CACHE_KEY_STATUS, array());
if (count($items) == 0) {

    $results = DB::select(DB::raw('SELECT locations.id, locations.slug, locations.key, (TIME_TO_SEC(TIMEDIFF(now(), locations_ips.created_at))/3600) < 2 as is_fresh, concat(cities.name, " > ", locations.name) as name, locations_ips.name as ip FROM locations join cities on locations.city_id = cities.id left outer join locations_ips on locations.id = locations_ips.id ORDER BY locations.name ASC, locations_ips.name ASC'));
    $items = array();
    foreach ($results as $result) {
        $items[] = array(
                'is_fresh' => $result->is_fresh,
                'slug' => $result->slug,
                'key' => $result->key,
                'name' => $result->name,
                'ip' => $result->ip);
    }

    Cache::put(CheckinController::CACHE_KEY_STATUS, $items, 5);
}

?>

@if(count($items) > 0)
    <div class="ibox">
        <div class="ibox-content">
            <div class="media-body">
                <table class="table table-hover no-margins">
                    <?php $is_first = true; ?>
                    @foreach($items as $index => $item)
                        <tr>
                            <td @if($is_first) class="no-borders" @endif>
                                <a href="{{ URL::route('api_location_update', array('location_slug' => $item['slug'], 'key' => $item['key'])) }}"
                                   class="btn btn-default btn-xs">
                                    <i class="fa fa-magnet"></i>
                                </a>
                                <span style="color: #676a6c">{{ trim($item['name'], ' > ') }}</span>

                            </td>
                            <td @if($is_first) class="no-borders" @endif>
                                @if($item['is_fresh'])
                                    @if($item['ip'])
                                        <div class="label label-primary">{{ $item['ip'] }}</div>
                                    @else
                                        -
                                    @endif
                                @else
                                    @if($item['ip'])
                                        <div class="label label-danger">{{ $item['ip'] }}</div>
                                    @else
                                        -
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <?php $is_first = false; ?>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endif