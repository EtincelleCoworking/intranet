<?php

use Illuminate\Http\Response;

class MonitoringController extends BaseController
{
    public function agents($location_slug, $location_key)
    {
        $items = DB::select(DB::raw(str_replace(array(':slug', ':key'), array($location_slug, $location_key),
            'SELECT equipment.ip, equipment.kind 
          FROM equipment 
            JOIN locations on locations.id = equipment.location_id
          WHERE locations.slug = ":slug" 
            AND locations.key = ":key"
            AND (equipment.last_seen_at IS NULL 
              OR (DATE_ADD(equipment.last_seen_at, INTERVAL equipment.frequency SECOND) < NOW()))
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

    public function feedback($location_slug, $location_key)
    {
        $feedback = json_decode(Request::getContent(), true);

        $equipments = Equipment::whereIn('ip', array_keys($feedback))
            ->join('locations', 'locations.id', '=', 'equipment.location_id')
            ->where('locations.slug', '=', $location_slug)
            ->where('locations.key', '=', $location_key)
            ->select('equipment.*')
            ->get();

        $count = 0;
        $ip_updated = false;
        $now = date('Y-m-d H:i:s');
        foreach ($equipments as $equipment) {
            if (!$ip_updated) {
                LocationIp::where('id', '=', $equipment->location_id)->delete();

                $locationIp = new LocationIp();
                $locationIp->id = $equipment->location_id;
                $locationIp->name = $_SERVER['REMOTE_ADDR'];
                $locationIp->save();

                $ip_updated = true;
            }
            $equipment->last_seen_at = $now;
            $equipment->storeData($feedback[$equipment->ip]);
            $equipment->save();
            $count++;
        }

        return new Response($count);
    }
}
