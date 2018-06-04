<?php

/**
 * Cashflow Controller
 */
class TeamPlanningController extends BaseController
{
    public function index()
    {
        $user_id = Input::get('user_id');
        $location_id = Input::get('location_id');
        if ($user_id) {
            $json_url = URL::route('planning_json') . '?user_id=' . $user_id;
        } elseif ($location_id) {
            $json_url = URL::route('planning_json') . '?location_id=' . $location_id;
        } else {
            $json_url = URL::route('planning_json');
        }
        $staff = User::staff()->get();
        $locations = Location::where('is_staffed', true)->get();

        return View::make('team_planning.index', array(
            'staff' => $staff,
            'locations' => $locations,
            'user_id' => $user_id,
            'location_id' => $location_id,
            'json_url' => $json_url
        ));
    }

    public function populate()
    {
        TeamPlanningItem::truncate();
        $days = array(1, 2, 3, 4, 5);
        $ranges = array(
            array('08:00' => '11:00', '15:00' => '19:00'),
            array('08:00' => '12:30', '13:30' => '16:00'),
            array('11:00' => '14:30', '15:30' => '19:00'),
        );
        $members = array();
        $members[] = 1; // Sébastien
        $members[] = 877; // Aurélie
        $members[] = 1474; // Caroline
        foreach ($members as $planning_index => $user_id) {
            $count = 31;
            $now = mktime(0, 0, 0, 6, 1, 2018);
            while ($count--) {
                if (in_array(date('N', $now), $days)) {
                    $range_index = $planning_index++ % 3;
                    foreach ($ranges[$range_index] as $start_time => $end_time) {
                        $item = new TeamPlanningItem();
                        $item->user_id = $user_id;
                        $item->location_id = ($range_index != 0) ? 1 : 8;
                        $item->start_at = date('Y-m-d ', $now) . $start_time;
                        $item->end_at = date('Y-m-d ', $now) . $end_time;
                        $item->save();
                        print_r($item);
                    }
                }
                $now = strtotime('+1 day', $now);
            }
        }
    }

    protected function getColors()
    {
        $colors = array();
        $colors[1] = array( // Sébastien
            'text' => '#ffffff',
            'background' => '#40A040',
            'border' => '#008000',
        );
        $colors[877] = array( // Aurélie
            'text' => '#ffffff',
            'background' => '#FFBC40',
            'border' => '#FFA500',
        );
        $colors[1474] = array( // Caroline
            'text' => '#ffffff',
            'background' => '#6CA5C2',
            'border' => '#3A87AD',
        );
        return $colors;
    }

    public function json()
    {
        $result = array();
        $events = TeamPlanningItem::join('users', 'team_planning_item.user_id', '=', 'users.id')
            ->join('locations', 'team_planning_item.location_id', '=', 'locations.id')
            ->where('users.is_staff', true)
            ->where('start_at', '<', Input::get('end'))
            ->where('end_at', '>', Input::get('start'))
            ->select('team_planning_item.*');
        if ($user_id = Input::get('user_id')) {
            $events->where('users.id', '=', $user_id);
            $single_user = true;
        } else {
            $single_user = false;
        }
        if ($location_id = Input::get('location_id')) {
            $events->where('locations.id', '=', $location_id);
            $single_location = true;
        } else {
            $single_location = false;
        }
        $colors = $this->getColors();
        $ressources = array();

        foreach ($events->get() as $event) {
            if ($single_user) {
                $title = sprintf('%s', $event->location->name);
            } else {
                if ($single_location) {
                    $title = sprintf('%s', $event->user->firstname);
                } else {
                    $title = sprintf('%s - %s', $event->user->firstname, $event->location->name);
                }
            }
            //$title = sprintf('%s - %s', $event->user->firstname, $event->location->name);
            $result[] = array(
                'id' => $event->id,
                'title' => $title,
                'start' => $event->start_at,
                'end' => $event->end_at,
                'url' => URL::route('planning_modify', $event->id),
                'textColor' => $colors[$event->user_id]['text'],
                'backgroundColor' => $colors[$event->user_id]['background'],
                'borderColor' => $colors[$event->user_id]['border'],
                'resourceId' => $event->location_id,
            );
            $ressources[$event->location_id] = true;
        }

        $events = BookingItem::join('ressources', 'booking_item.ressource_id', '=', 'ressources.id')
            ->join('locations', 'ressources.location_id', '=', 'locations.id')
            ->where('booking_item.start_at', '<', Input::get('end'))
            ->where(DB::raw('DATE_ADD(start_at, INTERVAL duration MINUTE)'), '>', Input::get('start'))
            ->with('ressource')
            ->select('booking_item.*');
        if ($location_id) {
            $events->where('locations.id', '=', $location_id);
        }
        foreach ($events->get() as $event) {
            $result[] = array(
                'id' => sprintf('bg%d', $event->ressource->location_id),
                'start' => $event->start_at,
                'end' => date('Y-m-d H:i', strtotime($event->start_at) + 60 * $event->duration),
                'rendering' => 'background',
                'resourceId' => $event->ressource->location_id,
            );
            unset($ressources[$event->ressource->location_id]);
        }
        foreach (array(1, 8) as $location_id) {
            $result[] = array(
                'id' => sprintf('bg%d', $location_id),
                'start' => Input::get('start'),
                'end' => date('Y-m-d H:i', strtotime(Input::get('start')) + 1),
                'rendering' => 'background',
                'resourceId' => $location_id,
            );
        }
        return Response::json($result);
    }


    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = TeamPlanningItem::find($id);
        if (!$data) {
            return Redirect::route('planning_list')->with('mError', 'Cet élément est introuvable !');
        } else {
            return $data;
        }
    }

    public function liste()
    {
        return Redirect::route('planning');
        // dsqdsq
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_city_id') && !empty(Input::get('filtre_city_id'))) {
                Session::put('filtre_subscription.city_id', Input::get('filtre_city_id'));
            } else {
                Session::forget('filtre_subscription.city_id');
            }
            if (Input::has('filtre_organisation_id')) {
                Session::put('filtre_subscription.organisation_id', Input::get('filtre_organisation_id'));
            } else {
                Session::forget('filtre_subscription.organisation_id');
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_subscription.user_id', Input::get('filtre_user_id'));
            } else {
                Session::forget('filtre_subscription.user_id');
            }
        }

        $items = TeamPlanningItem::join('users', 'team_planning_item.user_id', '=', 'users.id')
            ->join('locations', 'team_planning_item.location_id', '=', 'locations.id')
            ->orderBy('team_planning_item.start_at', 'asc')
            ->select('team_planning_item.*');;
        if (Session::has('filtre_subscription.user_id')) {
            $items->where('subscription.user_id', '=', Session::get('filtre_subscription.user_id'));
        }
        if (Session::has('filtre_subscription.organisation_id')) {
            $items->where('subscription.organisation_id', '=', Session::get('filtre_subscription.organisation_id'));
        }
        if (Session::has('filtre_subscription.city_id')) {
            $items->where('locations.city_id', '=', Session::get('filtre_subscription.city_id'));
        }

        return View::make('team_planning.liste', array(
            'items' => $items->paginate(15)));

    }

    /**
     * Modify ressource
     */
    public function modify($id)
    {
        $item = $this->dataExist($id);
        if (!$item instanceof TeamPlanningItem) {
            return $item;
        }

        return View::make('team_planning.modify', array('item' => $item));
    }

    /**
     * Modify ressource (form)
     */
    public function modify_check($id)
    {
        $item = $this->dataExist($id);

        $validator = Validator::make(Input::all(), TeamPlanningItem::$rules);
        if (!$validator->fails()) {

            $messages = array();
            if (!preg_match('#^[0-9]{2}/[0-9]{2}/[0-9]{4}$#', Input::get('date'))) {
                $messages['date'] = 'La date doit être renseignée';
            }
            if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('start'))) {
                $messages['start'] = 'L\'heure de début doit être renseignée';
            }
            if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('end'))) {
                $messages['end'] = 'L\'heure de fin doit être renseignée';
            }

            if (count($messages) > 0) {
                return Redirect::route('planning_modify', $item->id)->with('mError', 'Il y a des erreurs')->withErrors($messages)->withInput();
            }

            $start = newDateTime(Input::get('date'), Input::get('start'));
            $end = newDateTime(Input::get('date'), Input::get('end'));


            $item->start_at = $start->format('Y-m-d H:i');
            $item->end_at = $end->format('Y-m-d H:i');
            $item->location_id = Input::get('location_id');
            $item->user_id = Input::get('user_id');

            if ($item->save()) {
                return Redirect::route('planning_list')->with('mSuccess', 'Ce planning a bien été modifiée');
            } else {
                return Redirect::route('planning_modify', $item->id)->with('mError', 'Impossible de modifier ce planning')->withInput();
            }
        } else {
            return Redirect::route('planning_modify', $item->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function delete($id)
    {
        $item = $this->dataExist($id);
        if (!$item instanceof TeamPlanningItem) {
            return $item;
        }

        $item->delete();

        return Redirect::route('planning_list')->with('mSuccess', 'Ce planning a bien été supprimé');
    }
}
