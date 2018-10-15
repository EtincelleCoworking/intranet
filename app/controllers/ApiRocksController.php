<?php

/**
 * UserController Class
 */
class ApiRocksController extends BaseController
{
    protected function validateCity($value)
    {
        $all_cities = City::getSlugs();
        $unknown_cities = array();
        $filter_city = array();

        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $city_slug) {
            if (isset($all_cities[$city_slug])) {
                $filter_city[] = $all_cities[$city_slug];
            } else {
                $unknown_cities[] = $city_slug;
            }
        }
        if (count($unknown_cities) > 0) {
            throw new \Exception(sprintf('Parameter filter[city] contains unknown value(s): %s', implode(', ', $unknown_cities)), 400);
        }
        return $filter_city;
    }
    protected function validateJob($value)
    {
        $all = Job::getSlugs();
        $unknown = array();
        $filtered = array();

        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $slug) {
            if (isset($all[$slug])) {
                $filtered[] = $all[$slug];
            } else {
                $unknown[] = $slug;
            }
        }
        if (count($unknown) > 0) {
            throw new \Exception(sprintf('Parameter filter[job] contains unknown value(s): %s', implode(', ', $unknown)), 400);
        }
        return $filtered;
    }

    protected function parseParameterFilter($value)
    {
        $result = array();
        if (!empty($value)) {
            if (!is_array($value)) {
                throw new \Exception('Parameter filter must be an array');
            }
            $valid_keys = ['city', 'job'];
            $unknown_keys = array_diff(array_keys($value), $valid_keys);
            if (count($unknown_keys) > 0) {
                throw new \Exception(sprintf('Parameter filter contains unknown value(s): %s', implode(', ', $unknown_keys)));
            }
            if (!empty($value['city'])) {
                $result['city'] = $this->validateCity($value['city']);
            }
            if (!empty($value['job'])) {
                $result['job'] = $this->validateJob($value['job']);
            }
        }
        return $result;
    }

    public function users()
    {
        $delay = '-6 week';
        $delay = '-2 month';

        try {
            $filter = $this->parseParameterFilter(Input::get('filter'));
        } catch (\Exception $e) {
            return Response::make($e->getMessage(), 400);
        }

        $sql_from = [' FROM users '];
        $sql_from[] = 'JOIN past_times ON past_times.user_id = users.id AND past_times.ressource_id = ' . Ressource::TYPE_COWORKING;
        $sql_where = ['coworking_started_at IS NOT NULL'];
        $sql_where[] = 'users.is_hidden_member = 0';
        $sql_where[] = sprintf('last_seen_at > "%s"', date('Y-m-d', strtotime($delay)));
        if (!empty($filter['city'])) {
            $sql_from[] = ' JOIN locations ON locations.id = users.default_location_id';
            $sql_where[] = sprintf(' locations.city_id IN (%s)', implode(',', $filter['city']));
        }
        if (!empty($filter['job'])) {
            $sql_from[] = ' JOIN user_job on user_job.user_id = users.id';
            $sql_where[] = sprintf(' user_job.job_id IN (%s)', implode(', ', $filter['job']));
        }
        $sql = 'SELECT users.id, users.firstname, users.lastname, users.slug, users.avatar, users.email, users.bio_short, MAX(past_times.time_start) as last_seen_at'
            . implode(' ', $sql_from)
            . ' WHERE ' . implode(' AND ', $sql_where)
            . ' GROUP BY users.id';;

//die($sql);
        $result = array();
        foreach (DB::select($sql) as $item) {
            $result['data'][] = array(
                'id' => $item->id,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'slug' => $item->slug,
                'email' => $item->email,
                'bio_short' => $item->bio_short,
                'picture_url' => User::AvatarUrl($item->id, $item->email, $item->avatar, 300),
                'job' => null,
            );
        }
        return Response::json($result);
    }

    public function cities()
    {
        $active_cities = City::join('locations', 'locations.city_id', '=', 'cities.id')
            ->where('locations.is_active', '=', true)
            ->groupBy('cities.id')
            ->select('cities.name', 'cities.id')->get()->toArray();

        $result = array('data' => array());
        foreach ($active_cities as $item) {
            $result['data'][] = array(
                'id' => $item['id'],
                'slug' => strtolower($item['name']),
                'name' => $item['name']
            );
        }
        return Response::json($result);
    }


    public function jobs($parent_id = null)
    {
        $sql = 'SELECT jobs.name, jobs.slug, COUNT(user_job.user_id) as cnt 
          FROM jobs JOIN user_job ON user_job.job_id = jobs.id GROUP BY jobs.id';
        if (!empty($parent_id)) {
            $sql .= sprintf(' WHERE jobs.parent_id = %d', $parent_id);
        }
        $sql .= 'HAVING cnt > 0 ORDER BY name ASC';
        $active_cities = DB::select($sql);

        $result = array('data' => array());
        foreach ($active_cities as $item) {
            $result['data'][] = array(
                'slug' => $item->slug,
                'name' => $item->name,
                'count' => $item->cnt,
            );
        }
        return Response::json($result);
    }

    public function user($user_slug)
    {
        $user = User::where('slug', '=', $user_slug)->first();
        if (null == $user) {
            return Response::make('Unknown user', 404);
        }
        $result = array(
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        );
        return Response::json(['data' => $result]);
    }
}

