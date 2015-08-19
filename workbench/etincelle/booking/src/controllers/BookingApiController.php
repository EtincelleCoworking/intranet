<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BookingApiController extends Controller
{
    public function members($booking_item_id)
    {
        $result = array();
        $members = array();
        $users = User::join('booking_item_user', 'users.id', '=', 'booking_item_user.users_id')
            ->join('booking_item', 'booking_item_user.booking_item_id', '=', 'booking_item.id')
            ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
            ->where('booking_item.id', '=', $booking_item_id)
            ->orderBy('booking_item_user.created_at')
            ->get(array('users.*'));
        foreach ($users as $user) {
            $members[] = $user->id;
            $result[] = $this->getMember($user);
        }
//        $queries = DB::getQueryLog();
//        echo '<pre>';
//        print_r($queries); exit;
        return Response::json(array('is_member' => in_array(Auth::id(), $members), 'members' => $result));
    }

    protected function getMember($user)
    {
        return array(
            'id' => $user->id,
            'fullname' => $user->fullname,
            'profile_url' => URL::route('user_profile', $user->id),
            'avatar_url' => $user->getGravatarUrl(48)
        );
    }

    public function register($booking_item_id, $user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Auth::id();
        }
        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $user_id)) {
            App::abort(403);
        }
        $item = new BookingItemUser();
        $item->users_id = $user_id;
        $item->booking_item_id = $booking_item_id;
        $item->save();

        $user = User::find($user_id);
        $booking_item = BookingItem::find($booking_item_id);
        Mail::send('booking::emails.register', array('booking_item' => $booking_item, 'user' => $user), function ($m) use ($user, $booking_item) {
            $m->from('sebastien@coworking-toulouse.com', 'Sébastien Hordeaux')
                ->bcc('sebastien@coworking-toulouse.com', 'Sébastien Hordeaux')
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('Etincelle Coworking - Inscription - %s', $booking_item->booking->title));
        });

        return Response::json(array('status' => 'OK', 'member' => $this->getMember(User::find($user_id))));
    }

    public function unregister($booking_item_id, $user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Auth::id();
        }
        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $user_id)) {
            App::abort(403);
        }
        BookingItemUser::where('users_id', '=', $user_id)
            ->where('booking_item_id', '=', $booking_item_id)
            ->delete();

        $user = User::find($user_id);
        $booking_item = BookingItem::find($booking_item_id);
        Mail::send('booking::emails.unregister', array('booking_item' => $booking_item, 'user' => $user), function ($m) use ($user, $booking_item) {
            $m->from('sebastien@coworking-toulouse.com', 'Sébastien Hordeaux')
                ->bcc('sebastien@coworking-toulouse.com', 'Sébastien Hordeaux')
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('Etincelle Coworking - Désinscription - %s', $booking_item->booking->title));
        });


        return Response::json(array('status' => 'OK', 'user_id' => $user_id));
    }
}