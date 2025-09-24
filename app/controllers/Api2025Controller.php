<?php

use Illuminate\Support\Facades\Hash;


class Api2025Controller extends BaseController
{
    public function user_list()
    {
        $lookup_email = Request::get('email');

        $query = User::limit(10);
        if ($lookup_email) {
            $lookup_email = strtoupper($lookup_email);
            $query->where('email', '=', $lookup_email);
        }
        $result = [];
        foreach ($query->get() as $user) {
            $result[] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
            ];
        }
        return new \Illuminate\Http\JsonResponse(['data' => $result]);
    }
    public function organisation_list()
    {
        $lookup= Request::get('name');

        $query = Organisation::limit(10);
        if ($lookup) {
            $lookup_email = strtoupper($lookup);
            $query->where('name', '=', $lookup_email);
        }
        $result = [];
        foreach ($query->get() as $organisation) {
            $result[] = [
                'id' => $organisation->id,
                'name' => $organisation->name,
                'address' => $organisation->address,
                'zipcode' => $organisation->zipcode,
                'city' => $organisation->city,
            ];
        }
        return new \Illuminate\Http\JsonResponse(['data' => $result]);
    }

    public function user_add()
    {
        $json = json_decode(Request::getContent());

        $user = User::where('email', '=', $json->email)->first();
        if ($user) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('There is already a user with that email: %s (#%d)', $json->email, $user->id)
            ]);
        }
        $user = new User();
        $user->firstname = $json->firstname ? $json->firstname : '';
        $user->lastname = $json->lastname ? $json->lastname : '';
        $user->password = Hash::make('etincelle');
        $user->email = $json->email;
        $user->phone = $json->phone_number;
        $user->save();

        return new \Illuminate\Http\JsonResponse([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
            ]
        ]);
    }
    public function organisation_add()
    {
        $json = json_decode(Request::getContent());


        $country = Country::where('name', '=', 'France')->first();
        if (!$country) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => 'Unable to find France country'
            ]);

        }

        $organisation = Organisation::where('name', '=', $json->name)->first();
        if ($organisation) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('There is already an organisation with that name: %s (#%d)', $json->name, $organisation->id)
            ]);
        }
        $organisation = new Organisation();
        $organisation->name = $json->name ? $json->name : '';
        $organisation->address = $json->address ? $json->address : '';
        $organisation->zipcode = $json->zipcode ? $json->zipcode : '';
        $organisation->city = $json->city ? $json->city : '';
        $organisation->country_id = $country->id;
        $organisation->save();

        return new \Illuminate\Http\JsonResponse([
            'status' => 'success',
            'data' => [
                'id' => $organisation->id,
                'name' => $organisation->name,
                'address' => $organisation->address,
                'zipcode' => $organisation->zipcode,
                'city' => $organisation->city,
            ]
        ]);
    }

    public function user_search()
    {
        $lookup = trim(Request::get('q'));
        if (strlen($lookup) < 3) {
            return \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('The query parameter %s is too short.', $lookup)
            ]);
        }

        $query = User::where('users.email', 'LIKE', '%' . $lookup . '%')
            ->groupBy(('users.id'));
        $query->with('organisations');
        if (!(strpos($lookup, '.') !== false) || (strpos($lookup, '@') !== false)) {
            $query->orWhere('users.firstname', 'LIKE', '%' . $lookup . '%')
                ->orWhere('users.lastname', 'LIKE', '%' . $lookup . '%');
        }
        $query->orderBy('users.lastname', 'ASC');
        $query->orderBy('users.firstname', 'ASC');
        $query->limit(50);
        $result = [];
        foreach ($query->get() as $user) {
            $organisations = [];
            foreach ($user->organisations as $organisation) {
                $organisations[] = [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                    'address' => $organisation->address,
                    'zipcode' => $organisation->zipcode,
                    'city' => $organisation->city,
                ];
            }
            $result[] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'organisations' => $organisations
            ];
        }
        return new \Illuminate\Http\JsonResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }
    public function organisation_search()
    {
        $lookup = trim(Request::get('q'));
        if (strlen($lookup) < 3) {
            return \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('The query parameter %s is too short.', $lookup)
            ]);
        }

        $query = Organisation::where('organisations.name', 'LIKE', '%' . $lookup . '%');
        $query->orderBy('organisations.name', 'ASC');
        $query->limit(50);
        $result = [];
        foreach ($query->get() as $organisation) {
            $result[] = [
                'id' => $organisation->id,
                'name' => $organisation->name,
                'address' => $organisation->address,
                'zipcode' => $organisation->zipcode,
                'city' => $organisation->city,
            ];
        }
        return new \Illuminate\Http\JsonResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function booking_availability(){
        $start_at = Request::get('start_at');
        $ends_at = Request::get('ends_at');

        $query = Ressource::join('locations', 'locations.id', 'resources.location_id')
            ->where('locations.city_id', '=', 1)
            ->where('resources.is_bookable', '=', true)
            ->select('resources.id as resource_id',
                'resources.name as resource_name',
                'locations.id as location_id',
                'locations.name as location_name'
            )
            ->orderBy('locations.name', 'ASC')
            ->orderBy('resources.name', 'ASC');
        $result = [];
        $has_confirmed = [];
        foreach ($query->get() as $item) {
            $result[$item->resource_id] = [
                'id' => $item->resource_id,
                'name' => $item->resource_name,
                'location' => [
                    'id' => $item->location_id,
                    'name' => $item->location_name,
                ],
                'bookings' => []
            ];
            $has_confirmed[$item->resource_id] = false;
        }
        $query = Booking::join('booking_item', 'booking_item.id', '=', 'booking_item.booking_id')
            ->where(DB::raw('DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE)'), '>', $start_at)
            ->where('booking_item.start_at', '<', $ends_at)
            ->select('booking_item.id as booking_id',
                'booking.title as booking_title',
                'booking_item.start_at as booking_start',
                'booking_item.confirmed_at as booking_confirmed_at',
                'booking_item.confirmed_by_user_id as booking_confirmed_by_user_id',
                DB::raw('DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) as booking_end'),
                'booking_item.ressource_id as resource_id'
            )
            ->orderBy('booking_item.start_at', 'ASC');
        $required_users = [];
        foreach ($query->get() as $item) {
            $required_users[$item->booking_confirmed_by_user_id] = true;
        }
        $users = $this->loadUsers(array_keys($required_users));
        foreach ($query->get() as $item) {
            $item_data = [
                'id' => $item->booking_id,
                'title' => $item->booking_title,
                'start_at' => $item->booking_start,
                'ends_at' => $item->booking_end,
                'confirmed' => null,
            ];

            if ($item->booking_confirmed_at) {
                $item_data['confirmed'] = [
                    'at' => $item->booking_confirmed_at,
                    'by' => $users[$item->booking_confirmed_by_user_id]
                ];
                $has_confirmed[$item->resource_id] = true;
            }
            $result[$item->resource_id]['bookings'][] = $item_data;
        }
        foreach ($result as $resource_id => $resource_data) {
            if ($has_confirmed[$resource_id]) {
                $result[$resource_id]['status'] = 'unavailable';
            } else {
                if (count($result[$resource_id]['bookings']) === 0) {
                    $result[$resource_id]['status'] = 'available';
                } else {
                    $result[$resource_id]['status'] = 'maybe';
                }
            }
        }
        return new \Illuminate\Http\JsonResponse(array_values($result));

    }
}