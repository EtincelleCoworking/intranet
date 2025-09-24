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
        foreach ($query->get() as $user) {
            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'address' => $user->address,
                'zipcode' => $user->zipcode,
                'city' => $user->city,
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
}