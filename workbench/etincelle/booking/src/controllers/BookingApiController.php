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
            'avatar_url' => $user->getAvatarUrl(48)
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
            $m->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_address'], $_ENV['mail_name'])
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('%s - Inscription - %s', $_ENV['organisation_name'], $booking_item->booking->title));
        });
        if (Request::ajax()) {
            return Response::json(array('status' => 'OK', 'member' => $this->getMember(User::find($user_id))));
        }
        return Redirect::route('booking_item_show', array('id' => $booking_item_id));
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
            $m->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_address'], $_ENV['mail_name'])
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('%s - Désinscription - %s', $_ENV['organisation_name'], $booking_item->booking->title));
        });

        if (Request::ajax()) {
            return Response::json(array('status' => 'OK', 'user_id' => $user_id));
        }
        return Redirect::route('booking_item_show', array('id' => $booking_item_id));
    }

    public function intercom($location_slug, $key)
    {
        $result = DB::selectOne(DB::raw(str_replace(
            array(':location_slug', ':key'), array($location_slug, $key),
            'SELECT COUNT(booking_item.id) as cnt
          FROM booking_item
            JOIN ressources on ressources.id = booking_item.ressource_id
            JOIN locations on locations.id = ressources.location_id
          WHERE locations.slug = ":location_slug"
            AND locations.key = ":key"
            AND ressources.intercom_enabled = true
            AND DATE_SUB(booking_item.start_at, INTERVAL 15 MINUTE) < now()
            AND DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) > now()')));
        return $result->cnt ? 'Yes' : 'No';
    }
}