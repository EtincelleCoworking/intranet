<?php

/**
 * Cashflow Controller
 */
class TeamPlanningController extends BaseController
{
    public function index()
    {
        $staff = User::staff()->get();
        $locations = Location::where('is_staffed', true)->get();
        return View::make('team_planning.index', array(
            'staff' => $staff,
            'locations' => $locations,
        ));
    }

    public function member($user_id)
    {
        $staff = User::staff()->get();
        $locations = Location::where('is_staffed', true)->get();
        return View::make('team_planning.member', array(
            'staff' => $staff,
            'locations' => $locations,
            'user_id' => $user_id
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
                    foreach ($ranges[$planning_index++ % 3] as $start_time => $end_time) {
                        $item = new TeamPlanningItem();
                        $item->user_id = $user_id;
                        $item->location_id = 1;
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

    public function json_member()
    {
        $result = array();
        $events = TeamPlanningItem::join('users', 'team_planning_item.user_id', '=', 'users.id')
            ->where('users.is_staff', true);
        if ($user_id = Input::get('user_id')) {
            $events->where('users.id', '=', $user_id);
        }
        foreach ($events->get() as $event) {
            $result[] = array(
                'id' => $event->id,
                'title' => $event->user->fullname,
                'start' => $event->start_at,
                'end' => $event->end_at,
            );
        }
        return Response::json($result);
    }

}
