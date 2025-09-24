<?php

use App\Models\Phonenumber;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class Api2025Controller extends BaseController
{
    public function user_list($request)
    {
        $query = User::query();
        if ($lookup_email = $request->get('email')) {
            $lookup_email = strtoupper($lookup_email);
            $query->where('email', '=', $lookup_email);
        }
        $query->select('users.id',
            'users.firstname',
            'users.lastname',
            'users.email',
            'users.avatar'
        );
        $query->limit(10);
        $result = [];
        foreach ($query->get() as $user) {
            $result[] = $user->toArray();
        }
        return new \Illuminate\Http\JsonResponse(['data' => $result]);
    }

    public function user_add($request){
        $json = json_decode($request->getContent());

        $user = User::where('email', '=', $json->email)->get()->first();
        if ($user) {
            return new \Illuminate\Http\JsonResponse([
                'status' => 'failure',
                'message' => sprintf('There is already a user with that email: %s (#%d)', $json->email, $user->id)
            ]);
        }
        $user = new User();
        $user->firstname = $json->firstname ?$json->firstname: '';
        $user->lastname = $json->lastname ?$json->lastname: '';
        $user->password = Hash::make('etincelle');
        $user->email = $json->email;
        $user->phone = $json->phone_number;
        $user->save();

        return \Illuminate\Http\JsonResponse([
            'status' => 'success',
            'data' => $user->toArray()
        ]);
    }

    public function user_search($request){
        $lookup = trim($request->get('q'));
        if (strlen($lookup) < 3) {
            return JsonResponse([
                'status' => 'failure',
                'message' => sprintf('The query parameter %s is too short.', $lookup)
            ]);
        }

        $query = User::query();
        $query->with('organisations');
        if ((strpos($lookup, '.') !== false) || (strpos($lookup, '@') !== false)) {
            // restrict search to email
            $query->where('users.email', 'LIKE', '%' . $lookup . '%');
        } else {
            $query->where('users.email', 'LIKE', '%' . $lookup . '%')
                ->orWhere('users.firstname', 'LIKE', '%' . $lookup . '%')
                ->orWhere('users.lastname', 'LIKE', '%' . $lookup . '%');
        }
        $query->select('users.id',
            'users.firstname',
            'users.lastname',
            'users.email',
            'users.avatar'
        );
        $query->orderBy('users.lastname', 'ASC');
        $query->orderBy('users.firstname', 'ASC');
        $query->limit(50);
        $result = [];
        foreach ($query->get() as $user) {
            $result[] = $user->toArray();
        }
        return JsonResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }
}