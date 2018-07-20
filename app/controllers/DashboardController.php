<?php

/**
 * Cashflow Controller
 */
class DashboardController extends BaseController
{
    public function admin($target_period = null)
    {
        if (null == $target_period) {
            $target_period = date('Y-m');
        }
        $ressources = array();
        $items = DB::select(DB::raw(sprintf('select 
ressources.id, ressources.name, ressources.is_bookable,ressources.ressource_kind_id,
#if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `location_name`
if(`locations`.`name` is null,cities.name,locations.name) as `location_name` 

from `locations` 
left outer join cities on locations.city_id = cities.id
join ressources on ressources.location_id = locations.id
WHERE ressource_kind_id <> %d
order by ressource_kind_id asc, ressources.order_index asc', RessourceKind::TYPE_EXCEPTIONNAL)));
        foreach ($items as $item) {
            $stats = Ressource::getStatForRessource($item->id, $target_period);
            $data = array_shift($stats);
            if (!is_object($data) || ($data->occurs_at != $target_period)) {
                $data = new stdClass();
                $data->busy_rate = 0;
                $data->amount = 0;
            }

            if (
            (in_array($item->ressource_kind_id, array(RessourceKind::TYPE_MEETING_ROOM, RessourceKind::TYPE_PRIVATE_OFFICE))
                && $item->is_bookable)
                //    || !in_array($item->ressource_kind_id, array(RessourceKind::TYPE_MEETING_ROOM, RessourceKind::TYPE_PRIVATE_OFFICE))
            ) {

                $ressources[$item->location_name]['meeting_room'][$item->id] = array(
                    'name' => $item->name,
                    'is_bookable' => $item->is_bookable,
                    'ressource_kind_id' => $item->ressource_kind_id,
                    'busy_rate' => $data->busy_rate,
                    'amount' => $data->amount,
                );
            } elseif ($item->ressource_kind_id == RessourceKind::TYPE_PRIVATE_OFFICE) {
                $ressources[$item->location_name]['private_office'][$item->id] = array(
                    'name' => $item->name,
                    'amount' => $data->amount,
                );
            }
        }


        $sql = 'select 
users.id as user_id, users.firstname, users.lastname,
if(`locations`.`name` is null,cities.name,locations.name) as location_name,
 sum((UNIX_timestamp(time_end)-UNIX_timestamp( time_start ))/3600) as hours 

from past_times  
join users on users.id = past_times.user_id
join ressources on past_times.ressource_id = ressources.id
join locations on users.default_location_id = locations.id 
left outer join cities on locations.city_id = cities.id

where ressources.ressource_kind_id = ' . RessourceKind::TYPE_COWORKING . '
AND  date_format(time_start, "%Y-%m") = "' . $target_period . '"
GROUP BY user_id
ORDER by hours DESC';

        $items = DB::select(DB::raw($sql));
        $users = array();
        foreach ($items as $item) {
            $users[$item->user_id] = true;
        }
        $users_objects = User::find(array_keys($users));
        foreach ($users_objects as $users_object) {
            $users[$users_object->id] = $users_object;
        }

        foreach ($items as $item) {
            $ressources[$item->location_name]['coworking'][$item->user_id] = array(
                'hours' => $item->hours,
                'instance' => $users[$item->user_id]
            );
        }

        return View::make('dashboard.admin', array(
            'datas' => Location::getStats(),
            'target_period' => $target_period,
            'ressources' => $ressources,
        ));
    }

}
