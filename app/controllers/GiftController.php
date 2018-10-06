<?php

class GiftController extends \BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function photoshoot()
    {
        $data = GiftPhotoshootSession::with('slots', 'slots.user')
            ->where('occurs_at', '>', date('Y-m-d'))
            ->orderBy('occurs_at', 'ASC')
            ->get();

        $userGift = Auth::user()->getUserGift(GiftKind::PHOTOSHOOT);

        return View::make('gift.photoshoot', array(
            'data' => $data,
            'can_book' => is_object($userGift) && ($userGift->used_at == null)
        ));
    }

    public function photoshoot_book($gift_photoshoot_id)
    {
        $slot = GiftPhotoshootSlot::find($gift_photoshoot_id);
        if (!$slot) {
            throw new Exception(sprintf('Unknown photoshoot slot %d', $gift_photoshoot_id));
        }
        if ($slot->user_id) {
            return Redirect::route('gift_photoshoot')->with('mError', 'Ce créneau est déjà réservé');
        }

        $userGift = Auth::user()->getUserGift(GiftKind::PHOTOSHOOT);
        if (!$userGift) {
            throw new Exception(sprintf('User %s does not have this gift %s', Auth::id(), GiftKind::PHOTOSHOOT));
        }

        $userGift->used_at = sprintf('%s %s', $slot->session->occurs_at, $slot->start_at);
        $userGift->save();

        $slot->user_id = Auth::id();
        $slot->save();

        $slack_message = array(
            'text' => sprintf('%s a *réservé* sa place pour le <%s|shooting photo> le %s à %s',
                Auth::user()->fullname, URL::route('gift_photoshoot'),
                date('d/m/Y', strtotime($slot->session->occurs_at)), substr($slot->start_at, 0, 5)
            )
        );
        Slack::postMessage(Config::get('etincelle.slack_staff_toulouse'), $slack_message);

        return Redirect::route('gift_photoshoot')->with('mSuccess', 'Votre créneau a été réservé');

    }


    public function photoshoot_cancel($gift_photoshoot_id)
    {
        $slot = GiftPhotoshootSlot::find($gift_photoshoot_id);
        if (!$slot) {
            throw new Exception(sprintf('Unknown photoshoot slot %d', $gift_photoshoot_id));
        }
        if (!$slot->user_id) {
            return Redirect::route('gift_photoshoot')->with('mError', 'Ce créneau n\'est pas réservé');
        }

        if ($slot->user_id == Auth::id()) {
            $slot->user_id = null;
            $slot->save();

            $slack_message = array(
                'text' => sprintf('%s a *annulé* sa réservation pour le <%s|shooting photo> le %s à %s',
                    Auth::user()->fullname, URL::route('gift_photoshoot'), date('d/m/Y', strtotime($slot->session->occurs_at)), substr($slot->start_at, 0, 5)
                )
            );
            Slack::postMessage(Config::get('etincelle.slack_staff_toulouse'), $slack_message);

            $userGift = Auth::user()->getUserGift(GiftKind::PHOTOSHOOT);
            if ($userGift) {
                $userGift->used_at = null;
                $userGift->save();
            }

        }

        return Redirect::route('gift_photoshoot')->with('mSuccess', 'Votre réservation a été annulée');

    }

    public function index($user_id)
    {
        $kinds = GiftKind::orderBy('description', 'asc')->get();

        $user_gifts = array();
        foreach (UserGift::where('user_id', '=', $user_id)->with('kind')->get() as $user_gift) {
            $user_gifts[$user_gift->kind->code] = $user_gift;
        }

        return View::make('gift.index', array(
            'kinds' => $kinds,
            'user' => User::find($user_id),
            'user_gifts' => $user_gifts
        ));
    }

    public function enable($user_id, $kind)
    {
        $kind = GiftKind::where('code', '=', $kind)->first();

        $user_gift = new UserGift();
        $user_gift->user_id = $user_id;
        $user_gift->kind_id = $kind->id;
        $user_gift->used_at = null;
        $user_gift->save();

        return Redirect::route('user_gift', $user_id)->with('mSuccess', 'Le cadeau a été activé');
    }

    public function disable($user_id, $kind)
    {
        $kind = GiftKind::where('code', '=', $kind)->first();
        $user_gift = UserGift::where('user_id', '=', $user_id)
            ->where('kind_id', '=', $kind->id)
            ->first();
        $user_gift->delete();

        return Redirect::route('user_gift', $user_id)->with('mSuccess', 'Le cadeau a été désactivé');
    }
}
