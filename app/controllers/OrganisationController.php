<?php

/**
 * Organisation Controller
 */
class OrganisationController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = Organisation::find($id);
        if (!$data) {
            return Redirect::route('organisation_list')->with('mError', 'Ce organisme est introuvable !');
        } else {
            return $data;
        }
    }

    /**
     * List organisations
     */
    public function liste()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_organisation_id') && Input::get('filtre_organisation_id')) {
                Session::put('filtre_organisation.organisation_id', Input::get('filtre_organisation_id'));
            } else {
                Session::forget('filtre_organisation.organisation_id');
            }
            if (Input::has('filtre_domiciliation') && Input::get('filtre_domiciliation')) {
                Session::put('filtre_organisation.domiciliation', Input::get('filtre_domiciliation'));
            } else {
                Session::forget('filtre_organisation.domiciliation');
            }
        }

        $q = Organisation::orderBy('name', 'ASC');
        if (Session::has('filtre_organisation.organisation_id')) {
            $recapFilter = Session::get('filtre_organisation.organisation_id');
            if ($recapFilter) {
                $q->whereId($recapFilter);
            }
        }
        if (Session::has('filtre_organisation.domiciliation')) {
            $q->where('domiciliation_kind_id', '>', '0');
        }

        $organisations = $q->with('domiciliation_kind')->paginate(15);

        return View::make('organisation.liste', array('organisations' => $organisations));
    }

    public function cancelFilter()
    {
        Session::forget('filtre_organisation.organisation_id');
        Session::forget('filtre_organisation.domiciliation');
        return Redirect::route('organisation_list');

    }

    /**
     * Modify organisation
     */
    public function modify($id)
    {
        $organisation = $this->dataExist($id);

        return View::make('organisation.modify', array('organisation' => $organisation));
    }

    /**
     * Modify organisation (form)
     */
    public function modify_check($id)
    {
        $organisation = $this->dataExist($id);

        $validator = Validator::make(Input::all(), Organisation::$rules);
        if (!$validator->fails()) {
            $organisation->name = Input::get('name');
            $organisation->address = Input::get('address');
            $organisation->zipcode = Input::get('zipcode');
            $organisation->city = Input::get('city');
            $organisation->country_id = Input::get('country_id');
            $organisation->tva_number = Input::get('tva_number');
            $organisation->code_purchase = Input::get('code_purchase');
            $organisation->code_sale = Input::get('code_sale');
            $organisation->domiciliation_kind_id = Input::get('domiciliation_kind_id', null) ? Input::get('domiciliation_kind_id', null) : null;
            $organisation->domiciliation_start_at = $this->normalizeDate(Input::get('domiciliation_start_at'));
            $organisation->domiciliation_end_at = $this->normalizeDate(Input::get('domiciliation_end_at'));
            if (Input::get('accountant_id')) {
                $organisation->accountant_id = Input::get('accountant_id');
            } else {
                $organisation->accountant_id = null;
            }

            if ($organisation->save()) {
                return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'Cet organisme a bien été modifié');
            } else {
                return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Impossible de modifier cet organisme')->withInput();
            }
        } else {
            return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    private function normalizeDate($value)
    {
        if ($value) {
            $data = explode('/', $value);
            return $data[2] . '-' . $data[1] . '-' . $data[0];
        }
        return null;
    }

    /**
     * Add organisation
     */
    public function add()
    {
        return View::make('organisation.add');
    }

    /**
     * Add Organisation check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Organisation::$rulesAdd);
        if (!$validator->fails()) {
            $organisation = new Organisation(Input::all());
            $organisation->domiciliation_kind_id = Input::get('domiciliation_kind_id', null) ? Input::get('domiciliation_kind_id', null) : null;
            $organisation->domiciliation_start_at = $this->normalizeDate(Input::get('domiciliation_start_at'));
            $organisation->domiciliation_end_at = $this->normalizeDate(Input::get('domiciliation_end_at'));

            if ($organisation->save()) {
                return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'L\'organisme a bien été ajouté');
            } else {
                return Redirect::route('organisation_add')->with('mError', 'Impossible de créer cet organisme')->withInput();
            }
        } else {
            return Redirect::route('organisation_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add user
     */
    public function add_user($id)
    {
        $organisation = $this->dataExist($id);

        if (Input::get('user_id')) {
            if (!is_array(OrganisationUser::where('user_id', Input::get('user_id'))->where('organisation_id', $organisation->id)->get())) {
                $add = new OrganisationUser;
                $add->user_id = Input::get('user_id');
                $add->organisation_id = $organisation->id;

                if ($add->save()) {
                    return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'Cet utilisateur a bien été associé à la société');
                } else {
                    return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Impossible d\'associer cet utilisateur à cette société')->withInput();
                }
            } else {
                return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Cet utilisateur est déjà associé à cette société')->withInput();
            }
        } else {
            return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Merci de renseigner un utilisateur')->withInput();
        }
    }

    public function add_users($id)
    {
        $organisation = $this->dataExist($id);

        if (Input::get('content')) {
            foreach (explode("\n", Input::get('content')) as $line) {
                $data = User::SplitNameEmail($line);
                $email = strtolower($data['email']);
                $user = User::where('email', '=', $email)->first();
                if (!$user) {
                    $user = new User();
                    $user->firstname = $data['firstname'];
                    $user->lastname = $data['lastname'];
                    $user->email = $data['email'];
                    $user->password = Hash::make('etincelle');
                    $user->default_location_id = Auth::user()->default_location_id;
                    $user->save();
                }
                $users[$user->id] = $user;
            }

            $already_linked = OrganisationUser::whereIn('user_id', array_keys($users))->where('organisation_id', $organisation->id)->get();
            foreach ($already_linked as $linked_user) {
                unset($users[$linked_user->user_id]);
            }
            $count = 0;
            foreach ($users as $user) {
                $link = new OrganisationUser();
                $link->user_id = $user->id;
                $link->organisation_id = $organisation->id;
                $link->save();
                $count++;
            }

            if ($count) {
                return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', sprintf('%d utilisateur(s) ont étés ajoutés', $count));
            }
            return Redirect::route('organisation_modify', $organisation->id)->withErrors('Aucun utilisateur trouvé')->withInput();

        } else {
            return Redirect::route('organisation_modify', $organisation->id)->withInput();
        }
    }

    /**
     * User add
     */
    public function user_add($id)
    {
        if (Input::get('organisation_id')) {
            if (!is_array(OrganisationUser::where('user_id', $id)->where('organisation_id', Input::get('organisation_id'))->get())) {
                $add = new OrganisationUser;
                $add->user_id = $id;
                $add->organisation_id = Input::get('organisation_id');

                if ($add->save()) {
                    return Redirect::route('user_modify', $id)->with('mSuccess', 'Cet utilisateur a bien été associé à la société');
                } else {
                    return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Impossible d\'associer cet utilisateur à cette société')->withInput();
                }
            } else {
                return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Cet utilisateur est déjà associé à cette société')->withInput();
            }
        } else {
            return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Merci de renseigner un utilisateur')->withInput();
        }
    }

    /**
     * Delete user
     */
    public function delete_user($organisation, $id)
    {
        if (OrganisationUser::where('organisation_id', $organisation)->where('user_id', $id)->delete()) {
            return Redirect::route('organisation_modify', $organisation)->with('mSuccess', 'Cet utilisateur a bien été retiré de cette société');
        } else {
            return Redirect::route('organisation_modify', $organisation)->with('mError', 'Impossible de retirer cet utilisateur');
        }
    }

    /**
     * Get infos from an organisation (JSON)
     */
    public function json_infos($id)
    {
        $organisation = Organisation::where('id', $id)->get()->lists('fulladdress', 'id');
        return Response::json($organisation);
    }

    /**
     * Json list
     */
    public function json_list()
    {
        if (strlen(Input::get('term')) >= 2) {
            $list = Organisation::where('name', 'LIKE', '%' . Input::get('term') . '%')->lists('name', 'id');
        } else {
            $list = array();
        }

        $ajaxArray = array();
        foreach ($list as $key => $value) {
            $ajaxArray[] = array(
                "id" => $key,
                "name" => $value
            );
        }

        return Response::json($ajaxArray);
    }


    public function remind($id)
    {
        $organisation = $this->dataExist($id);

        $pending_invoices = array();
        foreach ($organisation->invoices as $invoice) {
            if ($invoice->type == 'F' && !$invoice->date_payment && !$invoice->on_hold) {
                $pending_invoices[$invoice->date_invoice] = $invoice;
            }
        }
        ksort($pending_invoices);

        $content = "<p>Bonjour,</p><p>Sauf erreur ou omission de notre part, ";

        if (count($pending_invoices) == 1) {
            $invoice = current($pending_invoices);
            $amount = Invoice::TotalInvoiceWithTaxes($invoice->items);
            $content .= sprintf('le paiement de la facture n°%s datée du %s pour un montant de %s euros, et payable à réception de facture ne nous est pas parvenu.</p>',
                $invoice->ident, date('d/m/y', strtotime($invoice->date_invoice)), $amount);
            $content .= "<p>Nous vous prions de bien vouloir procéder à son règlement dans les meilleurs délais, et vous adressons, à toutes fins utiles, un duplicata de cette facture en pièce jointe.</p>";
        } else {
            $content .= "le paiement des factures suivantes ne nous est pas parvenu:</p><ul>";
            $total = 0;
            foreach ($pending_invoices as $invoice) {
                $amount = Invoice::TotalInvoiceWithTaxes($invoice->items);
                $content .= sprintf("<li>Facture n°%s datée du %s pour un montant de %s euros</li>",
                    $invoice->ident, date('d/m/y', strtotime($invoice->date_invoice)), $amount);
                $total += $amount;
            }
            $content .= sprintf("</ul><p>Soit un total de %s euros.</p>", $total);
            $content .= "<p>Ces factures sont payables à réception de facture.</p>";
            $content .= "<p>Nous vous prions de bien vouloir procéder à leur règlement dans les meilleurs délais, et vous adressons, à toutes fins utiles, un duplicata de ces facture en pièce jointe.</p>";
        }

        $content .= '<p>Vous remerciant de faire le nécessaire, et restant à votre entière disposition pour toute question, nous vous prions d\'agréer, l\'expression de nos salutations distinguées. </p>';
        return View::make('organisation.remind', array(
            'organisation' => $organisation,
            'invoices' => $pending_invoices,
            'content' => str_replace('</p>', "</p>\n", $content),

        ));

    }

    public function remind_send($id)
    {
        $organisation = $this->dataExist($id);
        $content = Input::get('content');
        $invoice_ids = Input::get('invoices');

        $invoices = Invoice::whereIn('id', $invoice_ids)->get();
        if (count($invoices) == 0) {
            return Redirect::route('invoice_unpaid')->with('mError', sprintf('Aucune facture sélectionnée pour la société %s', $organisation->name));
        }

        $target_user = null;
        if ($organisation->accountant) {
            $target_user = $organisation->accountant;
        } else {
            foreach ($invoices as $invoice) {
                if ($invoice->user) {
                    $target_user = $invoice->user;
                }
            }
        }

        if (!$target_user) {
            return Redirect::route('invoice_unpaid')->with('mError', sprintf('Impossible de trouver un contact à informer pour la société %s', $organisation->name));
        }
        Mail::send('emails.organisation_remind', array('content' => $content), function ($message) use ($organisation, $invoices, $target_user) {
            $message->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_address'], $_ENV['mail_name']);

            $message->to($target_user->email, $target_user->fullname);

            if (count($invoices) == 1) {
                $message->subject(sprintf('%s - Relance facture impayée', $_ENV['organisation_name']));

            } else {
                $message->subject(sprintf('%s - Relance factures impayées', $_ENV['organisation_name']));
            }

            $pdf = App::make('snappy.pdf.wrapper');
            foreach ($invoices as $invoice) {
                $message->attachData($pdf->getOutputFromHtml($invoice->getPdfHtml()),
                    sprintf('%s.pdf', $invoice->ident), array('mime' => 'application/pdf'));
            }
        });

        $to = htmlentities(sprintf('%s <%s>', $target_user->fullname, $target_user->email));

        foreach ($invoices as $invoice) {
            if (!$invoice->reminder1_at) {
                $invoice->reminder1_at = new \DateTime();
            } elseif (!$invoice->reminder2_at) {
                $invoice->reminder2_at = new \DateTime();
            } else {
                $invoice->reminder3_at = new \DateTime();
            }
            $invoice->save();

            $invoice_comment = new InvoiceComment();
            $invoice_comment->invoice_id = $invoice->id;
            $invoice_comment->user_id = Auth::user()->id;
            $invoice_comment->content = sprintf('Relance envoyée par email le %s à %s', date('d/m/Y'), $to);
            $invoice_comment->save();
        }

        $organisation->last_invoice_reminder_at = new \DateTime();
        $organisation->save();
        return Redirect::route('invoice_unpaid')->with('mSuccess', sprintf('L\'organisation %s a été relancée (%s)', $organisation->name, $to));
    }

    public function json_users($id)
    {
        return Response::json(User::SelectInOrganisation($id));
    }

    public function usage($id, $period = null)
    {
        if (null == $period) {
            $period = date('Y-m');
        }
        $start_at = $period . '-01 00:00:00';
        $end_at = date('Y-m-t 23:59:59', strtotime($start_at));
        $organisation = Organisation::find($id);
        $data = User::join('organisation_user', 'users.id', '=', 'organisation_user.user_id')
            ->where('organisation_user.organisation_id', $id)
            ->orderBy('users.lastname', 'ASC')
            ->get();
        $users = array();
        foreach ($data as $user) {
            $users[$user->user_id] = $user;
        }

        if (!isset($users[Auth::id()]) && !Auth::user()->isSuperAdmin()) {
            return Redirect::route('dashboard')->with('mError', 'Accès refusé');
        }

        $stats = DB::select(DB::raw(sprintf('SELECT 
past_times.user_id,
sum(time_to_sec(timediff(time_end, time_start )) / 3600) as used 
from past_times
join organisation_user on organisation_user.user_id = past_times.user_id 
where organisation_user.organisation_id = %1$d
# and past_times.is_free = 0
and past_times.time_start BETWEEN "%2$s" AND "%3$s"
and past_times.ressource_id = %4$d
group by past_times.user_id
', $id, $start_at, $end_at, Ressource::TYPE_COWORKING)));
        $coworking = array();
        foreach ($stats as $item) {
            $coworking[$item->user_id] = array(
                'hours' => floor($item->used),
                'minutes' => round(($item->used - floor($item->used)) * 60),
            );
        }


        $stats = DB::select(DB::raw(sprintf('SELECT 
booking.user_id,
ressources.name,
sum(booking_item.duration) / 60 as used 
from booking_item 
join booking on booking.id = booking_item.booking_id
join organisation_user on organisation_user.user_id = booking.user_id
join ressources on ressources.id = booking_item.ressource_id
where organisation_user.organisation_id = %1$d
and booking_item.start_at BETWEEN "%2$s" AND "%3$s"
and ressources.ressource_kind_id = %4$d
group by booking.user_id, booking_item.ressource_id
', $id, $start_at, $end_at, RessourceKind::TYPE_MEETING_ROOM)));
        $rooms = array();
        foreach ($stats as $item) {
            $rooms[$item->user_id][$item->name] = array(
                'hours' => floor($item->used),
                'minutes' => round(($item->used - floor($item->used)) * 60),
            );
        }


        $stats = DB::select(DB::raw(sprintf('SELECT 
devices.mac, devices.user_id, devices.last_seen_at
from devices 
join organisation_user on organisation_user.user_id = devices.user_id
where organisation_user.organisation_id = %1$d
', $id)));
        $devices = array();
        foreach ($stats as $item) {
            $devices[$item->user_id][] = array('mac' => $item->mac, 'active' => !empty($item->last_seen_at));
        }

        return View::make('organisation.usage', array(
            'organisation' => $organisation,
            'users' => $users,
            'period' => $period,
            'coworking' => $coworking,
            'rooms' => $rooms,
            'devices' => $devices,
        ));
    }
}