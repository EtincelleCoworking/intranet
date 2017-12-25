<?php

/**
 * Cashflow Controller
 */
class DashboardController extends BaseController
{
    public function admin($target_period = null)
    {
        if(null == $target_period){
            $target_period = date('Y-m');
        }
        $ressources = array();
        $items = DB::select(DB::raw(sprintf('select 
ressources.id, ressources.name,
if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 

from `locations` 
left outer join cities on locations.city_id = cities.id
join ressources on ressources.location_id = locations.id
WHERE ressource_kind_id <> %d
order by ressource_kind_id asc, ressources.order_index asc', RessourceKind::TYPE_EXCEPTIONNAL)));
        foreach ($items as $item) {
            $ressources[$item->kind][$item->id] = $item->name;
        }

        $datas = Location::getStats();
        return View::make('dashboard.admin', array(
            'datas' => $datas,
            'target_period' => $target_period,
            'ressources' => $ressources,
        ));
    }

}
