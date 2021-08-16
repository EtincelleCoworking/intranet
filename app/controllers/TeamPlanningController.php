<?php

/**
 * TeamPlanningController Controller
 */
class TeamPlanningController extends BaseController
{
    const TEAM_SEBASTIEN = 1;
    const TEAM_JEHANNE = 2410;
    const TEAM_MARINA = 3509;
    const TEAM_TAMARA = 3666;
    const TEAM_ZOE = 3852;
    const TEAM_LINE_ROSE = 3867;
    const TEAM_VALENTIN = 3989;
    const TEAM_PAULINE = 4495;
    const TEAM_ANAIS = 4506;

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

    protected function generateTimesheet($start, $end, $ranges, $member_id, $location_id, $days = array(1, 2, 3, 4, 5))
    {
        $now = strtotime($start);
        $ends = strtotime($end);
        while ($now <= $ends) {
            $_now = date('Y-m-d', $now);
            if (in_array(date('N', $now), $days) && !Utils::isFerian($_now)) {
                foreach ($ranges as $start_time => $end_time) {
                    if (empty($this->existing_holidays[$member_id][$_now])) {
                        $item = new TeamPlanningItem();
                        $item->user_id = $member_id;
                        $item->location_id = $location_id;
                        $item->start_at = $_now . ' ' . $start_time;
                        $item->end_at = $_now . ' ' . $end_time;
                        $item->save();
                        //print_r($item);
                    }
                }
            }
            $now = strtotime('+1 day', $now);
        }
    }


    protected function generateHolidays($start, $end, $member_id, $days = array(1, 2, 3, 4, 5))
    {
        $now = strtotime($start);
        $ends = strtotime($end);
        while ($now <= $ends) {
            $_now = date('Y-m-d', $now);
            if (in_array(date('N', $now), $days) && !Utils::isFerian($_now)) {
                $item = new TeamPlanningItem();
                $item->user_id = $member_id;
                $item->location_id = 1;
                $item->start_at = $_now . ' 00:00:00';
                $item->end_at = $_now . ' 00:00:00';
                $item->is_holiday = true;
                $item->save();
                //print_r($item);
            }
            $now = strtotime('+1 day', $now);
        }
    }

    protected $existing_holidays;

    public function populate()
    {
//        $this->generateHolidays('2020-07-15', '2020-08-04', self::TEAM_MARINA);
//        $this->generateHolidays('2020-08-03', '2020-08-28', self::TEAM_JEHANNE);

        $location_wilson = 1;
        $location_alsace_lorraine = 8;
        $location_carmes = 2;


        // DELETE from `team_planning_item` WHERE start_at >= "2019-12-01"
        //TeamPlanningItem::truncate();

        //region Marina


        $sql = 'select distinct(date(start_at)) as occurs_at, user_id from team_planning_item where start_at > now() and is_holiday = true';
        $this->existing_holidays = [];
        foreach (\Illuminate\Support\Facades\DB::select($sql) as $row) {
            if (!isset($this->existing_holidays[$row->user_id])) {
                $this->existing_holidays[$row->user_id] = [];
            }
            $this->existing_holidays[$row->user_id][$row->occurs_at] = true;
        }

        $start_at = '2020-09-14';
        foreach ([self::TEAM_VALENTIN, self::TEAM_MARINA] as $member) {
            \Illuminate\Support\Facades\DB::delete('DELETE FROM team_planning_item WHERE start_at >= ? AND user_id = ? AND is_holiday = false', [$start_at, $member]);
        }

        $now = strtotime('2020-09-14');
        $ends = strtotime('2020-10-31');

        $current = $now;
        while ($current <= $ends) {
            $day = date('Y-m-d', $current);
            $this->generateTimesheet($day, $day, array('08:30' => '11:00', '12:45' => '17:15'), self::TEAM_VALENTIN, $location_carmes);
            $this->generateTimesheet($day, $day, array('08:30' => '11:00', '12:30' => '17:00'), self::TEAM_MARINA, $location_wilson);
            $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_alsace_lorraine);
            $this->generateTimesheet($day, $day, array('08:15' => '11:00', '13:00' => '15:30'), self::TEAM_JEHANNE, $location_wilson);
            $current += 24 * 3600;
           /*
            // lundi
            $day = date('Y-m-d', $current);
            $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
            $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_MARINA, $location_alsace_lorraine);
            $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_wilson);

            // mardi
            $day = date('Y-m-d', strtotime($day) + 24 * 3600);
            if ($day <= $ends) {
                $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_MARINA, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_wilson);
            }
            // mercredi
            $day = date('Y-m-d', strtotime($day) + 24 * 3600);
            if ($day <= $ends) {
                $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_MARINA, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_wilson);
            }
            // jeudi
            $day = date('Y-m-d', strtotime($day) + 24 * 3600);
            if ($day <= $ends) {
                $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_MARINA, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
                $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_wilson);
            }
            // vendredi
            $day = date('Y-m-d', strtotime($day) + 24 * 3600);
            if ($day <= $ends) {
                if (date('W', $current) % 2) {
                    $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
                    $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_MARINA, $location_alsace_lorraine);
                } else {
                    $this->generateTimesheet($day, $day, array('08:00' => '11:00', '13:30' => '17:30'), self::TEAM_MARINA, $location_alsace_lorraine);
                    $this->generateTimesheet($day, $day, array('10:45' => '13:45', '15:00' => '19:00'), self::TEAM_LINE_ROSE, $location_alsace_lorraine);
                }
                $this->generateTimesheet($day, $day, array('08:00' => '12:30', '13:30' => '16:00'), self::TEAM_ZOE, $location_wilson);
            }
            $current += 7 * 24 * 3600;
           */
        }

        return 'OK';

    }

    protected function getColors()
    {
        $colors = array();
        $colors[self::TEAM_SEBASTIEN] = array(
            'text' => '#000000',
            'background' => '#A4C400',
            'border' => adjustBrightness('#A4C400', -32),
        );
        $colors[self::TEAM_PAULINE] = array(
            'text' => '#ffffff',
            'background' => '#00ABA9',
            'border' => adjustBrightness('#00ABA9', -32),
        );
        $colors[self::TEAM_JEHANNE] = array(
            'text' => '#ffffff',
            'background' => '#AA00FF',
            'border' => adjustBrightness('#AA00FF', -32),
        );
        $colors[self::TEAM_ANAIS] = array(
            'text' => '#ffffff',
            'background' => '#FA6800',
            'border' => adjustBrightness('#FA6800', -32),
        );
        /*
        $colors[2648] = array( // Suayip
            'text' => '#000000',
            'background' => '#E3C800',
            'border' => adjustBrightness('#E3C800', -32),
        );
        */
        /*
        $colors[2951] = array( // Lyne
            'text' => '#ffffff',
            'background' => '#6D8764',
            'border' => adjustBrightness('#6D8764', -32),
        );
*/
        $colors[self::TEAM_MARINA] = array( // Marina
            'text' => '#ffffff',
            'background' => '#6D8764',
            'border' => adjustBrightness('#6D8764', -32),
        );
        $colors[self::TEAM_TAMARA] = array( // Tamara
            'text' => '#ffffff',
            'background' => '#0050EF',
            'border' => adjustBrightness('#0050EF', -32),
        );
        $colors[self::TEAM_VALENTIN] = array(
            'text' => '#ffffff',
            'background' => '#0050EF',
            'border' => adjustBrightness('#0050EF', -32),
        );
        /*
                $colors[3667] = array( // Léa
                    'text' => '#ffffff',
                    'background' => '#FA6800',
                    'border' => adjustBrightness('#FA6800', -32),
                );
        */

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


    public function api()
    {
        $events = TeamPlanningItem::join('users', 'team_planning_item.user_id', '=', 'users.id')
            ->join('locations', 'team_planning_item.location_id', '=', 'locations.id')
            ->where('users.is_staff', true)
            ->where('is_holiday', false)
            ->where('start_at', '>=', date('Y-m-d 00:00:00'))
            ->where('end_at', '<=', date('Y-m-d 23:59:59'))
            ->with('location', 'user')
            ->orderBy('start_at', 'ASC')
            ->select('team_planning_item.*');

        $result = array();

        foreach ($events->get() as $event) {
            if (!isset($result[$event->user_id])) {
                $result[$event->user_id] = array(
                    'name' => $event->user->fullname,
                    'picture' => $event->user->avatarUrl,
                    'timesheets' => array()
                );
            }

            $result[$event->user_id]['timesheets'][] = array(
                'from' => $event->start_at,
                'to' => $event->end_at,
                'location' => $event->location->name
            );
        }
        $response = Response::json(array_values($result));
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        return $response;
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

