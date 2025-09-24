<?php

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


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
        $lookup = Request::get('name');

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

    public function booking_availability()
    {
        $start_at = Request::get('start_at');
        $ends_at = Request::get('ends_at');

        $query = Ressource::join('locations', 'locations.id', '=', 'ressources.location_id')
            ->where('locations.city_id', '=', 1)
            ->where('ressources.is_bookable', '=', true)
            ->select('ressources.id as resource_id',
                'ressources.name as resource_name',
                'locations.id as location_id',
                'locations.name as location_name'
            )
            ->orderBy('locations.name', 'ASC')
            ->orderBy('ressources.name', 'ASC');
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
        $query = Booking::join('booking_item', 'booking.id', '=', 'booking_item.booking_id')
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

            if ($item->booking_confirmed_at && isset($users[$item->booking_confirmed_by_user_id])) {
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

    public function booking_create_batch()
    {
        $json = json_decode(Request::getContent());
        try {
            $user = User::with('organisations')->findOrFail($json->user->id);
        } catch (\Exception $e) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('Unknown User #%d.', $json->user->id)
            ]);
        }
        try {
            $organisation = Organisation::findOrFail($json->organisation->id);
        } catch (\Exception $e) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('Unknown Organisation #%d.', $json->organisation->id)
            ]);
        }
        $result = [
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $this->avatarUrl($user->id, $user->email, $user->avatar),
                'organisations' => []

            ],
            'organisation' => $this->organisationToJsonFormat($organisation),
            'bookings' => [

            ],
        ];
        foreach ($user->organisations as $organisation) {
            $result['user']['organisations'][] = $this->organisationToJsonFormat($organisation);
        }
        foreach ($json->bookings as $index => $json_booking) {
            $start_at = sprintf('%s %s', $json_booking->occurs_at, $json_booking->start_at);
            $ends_at = sprintf('%s %s', $json_booking->occurs_at, $json_booking->ends_at);
            $instance = new Booking();
            $instance->user_id = $user->id;
            $instance->organisation_id = $organisation->id;
            $instance->title = $json_booking->title;
            $instance->save();

            $booking_item = new BookingItem();
            $booking_item->booking_id = $instance->id;
            $booking_item->start_at = $start_at;
            //$instance->ends_at = $json_booking->ends_at;
            $booking_item->duration = $this->getDuration($start_at, $ends_at);
            $booking_item->ressource_id = $json_booking->resource->id;
            $booking_item->save();
            $result['bookings'][$index] = [
                'id' => $booking_item->id,
                'resource' => ['id' => $booking_item->ressource_id],
                'occurs_at' => Carbon::parse($booking_item->start_at)->format('Y-m-d'),
                'start_at' => Carbon::parse($booking_item->start_at)->format('H:i'),
                'ends_at' => Carbon::parse($booking_item->start_at)->addMinutes($booking_item->duration)->format('H:i'),
            ];
        }
        return new \Illuminate\Http\JsonResponse(['status' => 'success', 'data' => $result]);
    }

    public function quote_create()
    {
        $json = json_decode(Request::getContent());
        try {
            $user = User::with('organisations')->findOrFail($json->user->id);
        } catch (\Exception $e) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('Unknown User #%d.', $json->user->id)
            ]);
        }
        try {
            $organisation = Organisation::findOrFail($json->organisation->id);
        } catch (\Exception $e) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('Unknown Organisation #%d.', $json->organisation->id)
            ]);
        }
        $booking_items = [];
        foreach ($json->bookings as $booking) {
            $booking_items[] = $booking->id;
        }
        $invoice = BookingController::createQuoteFromBookingItems($booking_items);
        $result = [
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $this->avatarUrl($user->id, $user->email, $user->avatar),
                'organisations' => []

            ],
            'organisation' => $this->organisationToJsonFormat($organisation),
            'quotes' => [
                [
                    'id' => $invoice->id,
                    'reference' => $invoice->ident,
                    'url' => route('invoice_print_pdf', ['id' => $invoice->id], true)
                ]
            ],
        ];
        foreach ($user->organisations as $organisation) {
            $result['user']['organisations'][] = $this->organisationToJsonFormat($organisation);
        }

        return new \Illuminate\Http\JsonResponse(['status' => 'success', 'data' => $result]);
    }

    protected function getDuration($start_at, $ends_at)
    {
        $start = explode(':', Carbon::parse($start_at)->format('H:i'));
        $end = explode(':', Carbon::parse($ends_at)->format('H:i'));
        return 60 * $end[0] + $end[1] - 60 * $start[0] - $start[1];
    }

    protected function organisationToJsonFormat($organisation)
    {
        return [
            'id' => $organisation->id,
            'name' => $organisation->name,
            'address' => $organisation->address,
            'zipcode' => $organisation->zipcode,
            'city' => $organisation->city,
        ];
    }

    protected function loadUsers($user_ids)
    {
        $result = [];
        if (count($user_ids) > 0) {
            $query = DB::select(DB::raw('SELECT users.id as user_id,
                users.firstname,
                users.lastname,
                users.email,
                users.avatar,
                organisations.id as organisation_id,
organisations.name as organisation_name,
organisations.address as organisation_address,
organisations.zipcode as organisation_zipcode,
organisations.city as organisation_city
FROM users LEFT OUTER JOIN organisation_user ON users.id = organisation_user.user_id
JOIN organisations ON organisation_user.organisation_id = organisations.id'));

            $organisations = [];
            foreach ($query as $item) {
                //dump($item);
                if ($item->organisation_id) {
                    $organisations[$item->organisation_id] = [
                        'id' => $item->organisation_id,
                        'name' => $item->organisation_name,
                        'address' => $item->organisation_address,
                        'zipcode' => $item->organisation_zipcode,
                        'city' => $item->organisation_city,
                    ];
                }
                if (!isset($result[$item->user_id])) {
                    $result[$item->user_id] = [
                        'id' => $item->user_id,
                        'firstname' => $item->firstname,
                        'lastname' => $item->lastname,
                        'email' => $item->email,
                        'avatar' => $this->avatarUrl($item->user_id, $item->email, $item->avatar),
                        'organisations' => $item->organisation_id ? [$organisations[$item->organisation_id]] : []
                    ];
                } else {
                    $result[$item->user_id]['organisations'][] = $organisations[$item->organisation_id];
                }
            }
        }
        return $result;
    }

    function avatarUrl($user_id, $user_email, $avatar_filename)
    {
        $size = 80;
        if (!empty($avatar_filename)) {
            $src_filename = sprintf('/uploads/users/%d/%s', $user_id, $avatar_filename);
            if (is_file(public_path() . $src_filename)) {
                $result = Croppa::url($src_filename, $size, $size, array('resize', 'pad'));
                //$result = preg_replace('!^(.+)\?.+$!', '$1', $result);
                return asset($result);
            }
        }
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($user_email))) . "?d=mm&s=" . $size;
    }
}