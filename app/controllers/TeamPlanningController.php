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
        // DELETE from `team_planning_item` WHERE user_id in (1, 2414, 877) and start_at >= "2019-01-03"

        //TeamPlanningItem::truncate();
        //region Jehanne
/*
        $ranges = array('08:15' => '11:00', '13:30' => '15:00');
        $days = array(1, 2, 3, 4, 5);
        $now = mktime(0, 0, 0, 3, 2, 2019);
        $ends = mktime(0, 0, 0, 6, 30, 2019);
        while ($now <= $ends) {
            if (in_array(date('N', $now), $days) && !Utils::isFerian(date('Y-m-d', $now))) {
                foreach ($ranges as $start_time => $end_time) {
                    $_now = date('Y-m-d', $now);
                    if (!Utils::isFerian($_now)) {
                        $item = new TeamPlanningItem();
                        $item->user_id = 2410;
                        $item->location_id = 1;
                        $item->start_at = $_now . ' ' . $start_time;
                        $item->end_at = $_now . ' ' . $end_time;
                        $item->save();
                        print_r($item);
                    }
                }
            }
            $now = strtotime('+1 day', $now);
        }
*/
        //endregion

        //region Lyne

        $ranges = array('08:00' => '10:30', '12:15' => '14:15', '15:15' => '17:45');
        $days = array(1, 2, 3, 4, 5);
        $now = mktime(0, 0, 0, 2, 26, 2019);
        $ends = mktime(0, 0, 0, 8, 5, 2019);
        while ($now <= $ends) {
            if (in_array(date('N', $now), $days) && !Utils::isFerian(date('Y-m-d', $now))) {
                foreach ($ranges as $start_time => $end_time) {
                    $_now = date('Y-m-d', $now);
                    if (!Utils::isFerian($_now)) {
                        $item = new TeamPlanningItem();
                        $item->user_id = 2951;
                        $item->location_id = 8;
                        $item->start_at = $_now . ' ' . $start_time;
                        $item->end_at = $_now . ' ' . $end_time;
                        $item->save();
                        print_r($item);
                    }
                }
            }
            $now = strtotime('+1 day', $now);
        }

        //endregion

        //region Suayip
        /*
        $ranges = array('08:30' => '12:30', '14:00' => '17:00');
        $planning = array();
        $planning['2018-10-30'] = 2;
        $planning['2018-11-02'] = 1;
        $planning['2018-11-05'] = 5;
        $planning['2018-11-19'] = 5;
        $planning['2018-11-26'] = 5;
        $planning['2018-12-17'] = 5;
        $planning['2018-12-24'] = 1;
        $planning['2018-12-26'] = 3;
        $planning['2018-12-31'] = 1;
        $planning['2019-01-02'] = 3;
        $planning['2019-01-14'] = 5;
        $planning['2019-01-21'] = 5;
        $planning['2019-02-04'] = 5;
        $planning['2019-02-11'] = 5;
        $planning['2019-02-25'] = 5;
        $planning['2019-03-11'] = 5;
        $planning['2019-03-18'] = 5;
        $planning['2019-04-01'] = 5;
        $planning['2019-04-08'] = 5;
        $planning['2019-04-23'] = 4;
        $planning['2019-04-29'] = 2;
        $planning['2019-05-02'] = 2;
        $planning['2019-05-13'] = 5;
        $planning['2019-05-20'] = 5;
        $planning['2019-06-01'] = 1;
        $planning['2019-06-03'] = 5;
        $planning['2019-06-11'] = 4;
        $planning['2019-06-24'] = 5;
        foreach ($planning as $now => $duration) {
            $now = strtotime($now);
            while ($duration--) {
                foreach ($ranges as $start_time => $end_time) {
                    $item = new TeamPlanningItem();
                    $item->user_id = 2648;
                    $item->location_id = 1;
                    $item->start_at = date('Y-m-d ', $now) . $start_time;
                    $item->end_at = date('Y-m-d ', $now) . $end_time;
                    $item->save();
                    print_r($item);
                }
                $now = strtotime('+1 day', $now);
            }
        }*/
//endregion
/*
        $days = array(1, 2, 3, 4, 5);
        $ranges = array(
            //array('08:00' => '12:30', '13:30' => '16:00'), // AL

            array('08:00' => '12:45', '14:00' => '16:00'), // Wilson

            array('11:00' => '14:30', '15:30' => '18:45'), // Wilson, repas sur place (12h30 / 13h30), checkout AL
        );
        $members = array();
        $members[] = 2414; // Julie
        //$members[] = 1; // Sébastien
        $members[] = 877; // Aurélie
        $ends = mktime(0, 0, 0, 6, 30, 2019);
        foreach ($members as $planning_index => $user_id) {
            $now = mktime(0, 0, 0, 1, 3, 2019);
            while ($now <= $ends) {
                if (in_array(date('N', $now), $days)) {
                    $_now = date('Y-m-d', $now);
                    if (!Utils::isFerian($_now)) {
                        $range_index = $planning_index++ % count($members);
                        foreach ($ranges[$range_index] as $start_time => $end_time) {
                            $item = new TeamPlanningItem();
                            $item->user_id = $user_id;
                            $item->location_id = 1; //($range_index != 0) ? 1 : 8;
                            $item->start_at = $_now . ' ' . $start_time;
                            $item->end_at = $_now . ' ' . ((($end_time == '18:45') && (date('N', $now) == 5)) ? '17:45' : $end_time);
                            $item->save();
                            print_r($item);
                        }
                    }
                }
                $now = strtotime('+1 day', $now);
            }
        }
        */
/*
        $ranges = array('08:15' => '12:15', '13:45' => '16:45');
        $member_index = 0;
        $members = array();
        $members[] = 1; // Sébastien
        $members[] = 877; // Aurélie
        $members[] = 1; // Sébastien
        $members[] = 2414; // Julie
        $ends = mktime(0, 0, 0, 6, 30, 2019);
        $now = mktime(0, 0, 0, 1, 5, 2019);
        while ($now <= $ends) {
            $_now = date('Y-m-d', $now);
            if (!Utils::isFerian($_now)) {
                foreach ($ranges as $start_time => $end_time) {
                    $item = new TeamPlanningItem();
                    $item->user_id = $members[$member_index % count($members)];
                    $item->location_id = 1; //($range_index != 0) ? 1 : 8;
                    $item->start_at = $_now . ' ' . $start_time;
                    $item->end_at = $_now . ' ' . $end_time;
                    $item->save();
                    print_r($item);
                }
                $member_index++;
            }
            $now = strtotime('+7 day', $now);
        }*/
    }

    protected function getColors()
    {
        $colors = array();
        $colors[1] = array( // Sébastien
            'text' => '#000000',
            'background' => '#FFD800',
            'border' => adjustBrightness('#FFD800', -32),
        );
        $colors[877] = array( // Aurélie
            'text' => '#ffffff',
            'background' => '#FF9400',
            'border' => adjustBrightness('#FF9400', -32),
        );
        $colors[1474] = array( // Caroline
            'text' => '#ffffff',
            'background' => '#EE1717',
            'border' => adjustBrightness('#EE1717', -32),
        );
        $colors[2410] = array( // Jehanne
            'text' => '#ffffff',
            'background' => '#EE1717',
            'border' => adjustBrightness('#EE1717', -32),
        );
        $colors[2414] = array( // Julie
            'text' => '#ffffff',
            'background' => '#12ADC9',
            'border' => adjustBrightness('#12ADC9', -32),
        );
        $colors[2648] = array( // Suayip
            'text' => '#000000',
            'background' => '#A6F94D',
            'border' => adjustBrightness('#A6F94D', -32),
        );
        return $colors;
    }

    public function json()
    {
        $result = array();
        $events = TeamPlanningItem::join('users', 'team_planning_item.user_id', '=', 'users.id')
            ->join('locations', 'team_planning_item.location_id', '=', 'locations.id')
            ->where('users.is_staff', true)
            ->where('start_at', '<=', Input::get('end'))
            ->where('end_at', '>=', Input::get('start'))
            ->with('location', 'user')
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
            if ($single_user || $event->is_holiday) {
                $title = $event->location->name;
            } else {
                if ($single_location) {
                    $title = $event->user->firstname;
                } else {
                    $title = sprintf('%s - %s', $event->user->firstname, $event->location->name);
                }
            }
            if ($event->is_holiday) {
                $title = sprintf('%s - Congés', $event->user->firstname);
                $start = substr($event->start_at, 0, 10);
                $end = null;
                $all_day = true;
            } else {
                $start = $event->start_at;
                $end = $event->end_at;
                $all_day = false;
            }
            $item = array(
                'id' => $event->id,
                'title' => $title,
                'start' => $start,
                'allDay' => $all_day,
                'url' => URL::route('planning_modify', $event->id),
                'textColor' => $colors[$event->user_id]['text'],
                'backgroundColor' => $colors[$event->user_id]['background'],
                'borderColor' => $colors[$event->user_id]['border'],
                'resourceId' => $event->location_id,
            );
            if ($end) {
                $item['end'] = $end;
            }
            $result[] = $item;
            $ressources[$event->location_id] = true;
        }

        $events = BookingItem::join('ressources', 'booking_item.ressource_id', '=', 'ressources.id')
            ->join('locations', 'ressources.location_id', '=', 'locations.id')
            ->where('booking_item.start_at', '<=', Input::get('end'))
            ->where(function ($query) {
                $query->whereNull('ressources.ignore_planning_until')
                    ->orWhere('booking_item.start_at', '>', 'ressources.ignore_planning_until');
            })
            ->where('locations.city_id', '=', Auth::user()->location->city_id)
            ->where(DB::raw('DATE_ADD(start_at, INTERVAL duration MINUTE)'), '>=', Input::get('start'))
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

    public function add()
    {
        $item = new TeamPlanningItem();
        if ($date = Input::get('date')) {
            if ($start = Input::get('start')) {
                $item->start_at = sprintf('%s %s', $date, $start);
            }
            if ($end = Input::get('end')) {
                $item->end_at = sprintf('%s %s', $date, $end);
            }
        }
        if ($user_id = Input::get('user_id')) {
            $item->user_id = $user_id;
        }
        if ($location_id = Input::get('location_id')) {
            $item->location_id = $location_id;
        }
        return View::make('team_planning.modify', array('item' => $item));
    }

    /**
     * Modify ressource (form)
     */
    public function modify_check($id = null)
    {
        if ($id) {
            $item = TeamPlanningItem::find($id);
            if (!$item) {
                return Redirect::route('planning_list')->with('mError', 'Cet élément est introuvable !');
            }
        } else {
            $item = new TeamPlanningItem();
        }

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
            $item->is_holiday = Input::get('is_holiday', false);

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

