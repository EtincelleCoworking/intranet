<?php

use Illuminate\Http\Response;

class MonitoringController extends BaseController
{
    public function agents()
    {
        $items = DB::select(DB::raw(str_replace(array(':slug', ':key'),
            array(Request::header('LOCATION_SLUG'), Request::header('LOCATION_KEY')),
            'SELECT equipment.ip, equipment.kind 
          FROM equipment 
            JOIN locations on locations.id = equipment.location_id
          WHERE locations.slug = ":slug" 
            AND locations.key = ":key"
            AND DATE_ADD(equipment.last_seen_at, INTERVAL equipment.frequency SECOND) < NOW()
          ')));

        $data = array();
        foreach ($items as $item) {
            $data[$item->ip] = $item->kind;
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }

    public function feedback()
    {
        $feedback = json_decode(Request::getContent());

        $equipments = Equipment::whereIn('ip', array_keys($feedback))
            ->join('locations', 'locations.id', '=', 'equipment.location_id')
            ->where('locations.slug', '=', Request::header('LOCATION_SLUG'))
            ->where('locations.key', '=', Request::header('LOCATION_KEY'))
            ->get();

        $now = date('Y-m-d H:i:s');
        $count = 0;
        foreach ($equipments as $equipment) {
            $equipment->last_seen_at = $now;
            $equipment->data = json_encode($feedback[$equipment->ip]);
            $equipment->save();
            $count++;
        }

        return new Response($count);
    }
}
