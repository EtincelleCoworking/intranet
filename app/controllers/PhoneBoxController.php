<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


class PhoneBoxController extends BaseController
{
    public function index($location_slug, $key, $box_id)
    {
        $phonebox = Phonebox::join('locations', 'location_id', '=', 'locations.id')
            ->where('locations.slug', '=', $location_slug)
            ->where('locations.key', '=', $key)
            ->where('order_index', '=', $box_id)
            ->with('active_session')
            ->firstOrFail();

        return View::make('phonebox.index',
            array(
                'location_slug' => $location_slug,
                'key' => $key,
                'phonebox' => $phonebox
            ));
    }

    public function auth($location_slug, $key, $box_id)
    {
        $phonebox = Phonebox::join('locations', 'location_id', '=', 'locations.id')
            ->where('locations.slug', '=', $location_slug)
            ->where('locations.key', '=', $key)
            ->where('order_index', '=', $box_id)
            ->with('active_session')
            ->firstOrFail();

        $user = User::where('personnal_code', Input::get('code'))->first();
        if (empty($user->personnal_code)) {
            $data = array(
                'status' => 'error',
                'message' => 'Utilisateur inconnu'
            );
        } else {
            if ($phonebox->active_session) {
                $data = array(
                    'status' => 'error',
                    'message' => 'Ce box est en cours d\'utilisation'
                );
            } else {
                // check user quota
                $used = $user->getTotalPhoneboxUsageOverLastPeriod();
                if ($used > Phonebox::QUOTA) {
                    $data = array(
                        'status' => 'error',
                        'message' => sprintf('Vous avez dépassé votre quota d\'utilisation (%d min)', $used)
                    );
                } else {
                    $data = array();
                    $data['username'] = $user->fullname;
                    $data['picture'] = $user->getAvatarUrl(150);

                    // Créer la session
                    $session = new PhoneboxSession();
                    $session->phoneBox_id = $phonebox->id;
                    $session->user_id = $user->id;
                    $session->started_at = new \DateTime();
                    $dt = clone $session->started_at;
                    $session->ended_at = $dt->add(new \DateInterval(sprintf('PT%dM',
                        min(Phonebox::QUOTA - $used, Phonebox::DEFAULT_DURATION)
                    )));
                    $session->save();

                    $phonebox->active_session_id = $session->id;
                    $phonebox->save();
                    $data['session_id'] = $session->id;
                    $data['max_duration'] = Phonebox::QUOTA - $used;
                }
            }
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }

    public function update($location_slug, $key, $box_id)
    {
        $session_id = Input::get('session_id');
        $duration = (int)Input::get('duration');
        if ($duration < Phonebox::DEFAULT_DURATION || $duration > Phonebox::QUOTA) {
            $data = array(
                'status' => 'error',
                'message' => 'Durée incorrecte'
            );
        } else {
            $session = PhoneboxSession::join('phonebox', 'phonebox_id', '=', 'phonebox.id')
                ->join('locations', 'location_id', '=', 'locations.id')
                ->where('locations.slug', '=', $location_slug)
                ->where('locations.key', '=', $key)
                ->where('phonebox.order_index', '=', $box_id)
                ->where('phonebox_session.id', '=', $session_id)
                ->with('user')
                ->with('phonebox')
                ->select('phonebox_session.*')
                ->firstOrFail();

            if ($session->phonebox->active_session_id != $session->id) {
                $data = array(
                    'status' => 'error',
                    'message' => sprintf('Cette session est expirée (%d / %d)', $session->phonebox->active_session_id, $session->id)
                );
            } else {
                if ($session->user->getTotalPhoneboxUsageOverLastPeriod() >= Phonebox::QUOTA - $duration) {
                    $data = array(
                        'status' => 'error',
                        'message' => 'Vous avez dépassé votre quota d\'utilisation'
                    );
                } else {
                    $dt = new \DateTime($session->start_at);
                    $session->ended_at = $dt->add(new \DateInterval(sprintf('PT%dM', $duration)));
                    $session->save();
                    $data = array(
                        'status' => 'OK',
                        'duration' => $duration
                    );
                }
            }
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }


    public function stop($location_slug, $key, $box_id)
    {
        $session_id = Input::get('session_id');

        $session = PhoneboxSession::join('phonebox', 'phonebox_id', '=', 'phonebox.id')
            ->join('locations', 'location_id', '=', 'locations.id')
            ->where('locations.slug', '=', $location_slug)
            ->where('locations.key', '=', $key)
            ->where('phonebox.order_index', '=', $box_id)
            ->where('phonebox_session.id', '=', $session_id)
            ->with('user')
            ->select('phonebox_session.*')
            ->firstOrFail();

        $session->ended_at = new \DateTime();
        $session->ended_auto = false;
        $session->save();

        $phonebox = $session->phonebox;
        $phonebox->active_session_id = null;
        $phonebox->save();

        $data = array('status' => 'OK');

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }
}
