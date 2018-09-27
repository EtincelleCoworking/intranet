<?php

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * PostboxController Controller
 */
class PostboxController extends BaseController
{
    public function index()
    {
        $organisationsQuery = Organisation::whereNotNull('domiciliation_kind_id')
            /*
                         ->where(function ($query) {
                            $query->whereNull('domiciliation_end_at')
                                ->orWhere('domiciliation_end_at', '>', date('Y-m-d'));
                        })
            */
            ->orderBy('name', 'ASC');
        if (!Auth::user()->isSuperAdmin()) {
            $organisationsQuery->where('accountant_id', '=', Auth::id());
        }

        return View::make('postbox.index', array(
            'organisations' => $organisationsQuery->get()
        ));
    }

    public function details($organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            if ($organisation->accountant_id != $user->id) {
                throw new AccessDeniedHttpException();
            }
        }

        $notifications = PostboxNotification::where('organisation_id', '=', $organisation->id)
            ->orderBy('occurs_at', 'DESC')
            ->with('items')
            ->with('reporter')
            ->get();

        if (!$user->isSuperAdmin()) {
            DB::statement(sprintf('UPDATE postbox_notification SET seen_at = NOW() WHERE organisation_id = %d', $organisation->id));
        }
        return View::make('postbox.details', array(
            'organisation' => $organisation,
            'notifications' => $notifications
        ));
    }

    public function notify($organisation_id)
    {
        $kinds = PostboxKind::get()->lists('name', 'id');
        $default_kind_id = array_keys($kinds);
        $organisation = Organisation::find($organisation_id);

        if (!$organisation->accountant) {
            return Redirect::route('organisation_modify', $organisation->id)
                ->with('mError', 'Vous devez définir un contact pour l\'envoi des notifications');
        }
        return View::make('postbox.notify', array(
            'organisation' => $organisation,
            'kinds' => $kinds,
            'default_kind_id' => array_shift($default_kind_id)
        ));
    }

    public function notify_handle($organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $data = Input::all();

        $notification = new PostboxNotification();
        $notification->reporter_id = Auth::id();
        $notification->occurs_at = Utils::convertDate($data['occurs_at']);
        $notification->organisation_id = $organisation->id;
        $notification->save();

        $count = 0;
        $items = array();
        foreach ($data['kind'] as $index => $kinds) {
            $item = new PostboxItem();
            $item->postbox_notification_id = $notification->id;
            $item->quantity = $data['quantity'][$index];
            $item->kind_id = $data['kind'][$index];
            $item->from_name = $data['from_name'][$index];
            $item->details = $data['details'][$index];
            $item->save();

            $items[] = $item;
            $count += $item->quantity;
        }

        $to = $organisation->accountant;
        Mail::send('emails.postbox', array('content' => $count, 'organisation' => $organisation,
            'notification' => $notification, 'items' => $items),
            function ($message) use ($count, $to, $organisation) {
                $message->from($_ENV['mail_address'], $_ENV['mail_name'])
                    ->bcc($_ENV['mail_bcc']);

                $message->to($to->email, $to->fullname);
                $message->subject(sprintf('%1$s - %2$d courrier%3$s reçu%3$s pour %4$s',
                    $_ENV['organisation_name'], $count, ($count > 1) ? 's' : '', $organisation->name));
            });

        return Redirect::route('postbox')->with('mSuccess', sprintf('La notification a été envoyée à %s &lt;%s&gt;', $to->fullname, $to->email));
    }

}
