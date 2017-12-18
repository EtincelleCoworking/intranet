<?php

/**
 * Past Time Controller
 */
class PastTimeController extends BaseController
{
    const COWORKING_HALF_DAY_PRICING = 10;
    const COWORKING_HALF_DAY_MAX_DURATION = 5;

    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        if (Auth::user()->isSuperAdmin()) {
            $data = PastTime::find($id);
        } else {
            $data = PastTime::whereUserId(Auth::user()->id)->find($id);
        }

        if (!$data) {
            return Redirect::route('pasttime_list')->with('mError', 'Ce temps passé est introuvable !');
        } else {
            return $data;
        }
    }

    public function liste($month = null)
    {
//        if (Input::has('filtre_month') && Input::has('filtre_year')) {
//            Session::put('filtre_pasttime.month', Input::get('filtre_month'));
//            Session::put('filtre_pasttime.year', Input::get('filtre_year'));
//        }

        $itemPerPage = 15;
        if (Input::has('filtre_submitted')) {
            if (Input::has('toinvoice')) {
                Session::put('filtre_pasttime.filtre_toinvoice', true);
                Session::forget('filtre_pasttime.user_id');
                Session::forget('filtre_pasttime.organisation_id');
                Session::put('filtre_pasttime.start', '2014-12-01');
                Session::put('filtre_pasttime.end', date('Y-12-31'));
            } else {
                if (Input::has('filtre_user_id')) {
                    Session::put('filtre_pasttime.user_id', Input::get('filtre_user_id'));
                    $itemPerPage = 1000;
                }
                if (Input::has('filtre_organisation_id')) {
                    Session::put('filtre_pasttime.organisation_id', Input::get('filtre_organisation_id'));
                    $itemPerPage = 1000;
                }
                if (Input::has('filtre_start')) {
                    $date_start_explode = explode('/', Input::get('filtre_start'));
                    Session::put('filtre_pasttime.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
                    if (!Input::has('filtre_user_id')) {
                        Session::forget('filtre_pasttime.user_id');
                    }
                }
                if (Input::has('filtre_end')) {
                    $date_end_explode = explode('/', Input::get('filtre_end'));
                    Session::put('filtre_pasttime.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
                } else {
                    Session::put('filtre_pasttime.end', date('Y-m-d'));
                }
                if (Input::has('filtre_toinvoice')) {
                    Session::put('filtre_pasttime.filtre_toinvoice', Input::get('filtre_toinvoice'));
                } else {
                    Session::put('filtre_pasttime.filtre_toinvoice', false);
                }
            }
        }
        if (Session::has('filtre_pasttime.start')) {
            $date_filtre_start = Session::get('filtre_pasttime.start');
            $date_filtre_end = Session::get('filtre_pasttime.end');
        } else {
            $date_filtre_start = date('Y-m-01');
            $date_filtre_end = date('Y-m-t');
        }

//        if (Session::has('filtre_pasttime.month')) {
//            $date_filtre_start = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-01';
//            $date_filtre_end = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-'.date('t', Session::get('filtre_pasttime.month'));
//        } else {
//            $date_filtre_start = date('Y-m').'-01';
//            $date_filtre_end = date('Y-m').'-'.date('t', Session::get('filtre_pasttime.month'));
//        }

        $recapFilter = false;
        $q = PastTime::whereBetween('date_past', array($date_filtre_start, $date_filtre_end));
        $q->select('past_times.*');
        $q->with('user', 'ressource');
        if (Session::get('filtre_pasttime.filtre_toinvoice')) {
            $q->where('invoice_id', 0);
            $q->where('is_free', false);
        }
        if (Auth::user()->isSuperAdmin()) {
            if (Session::has('filtre_pasttime.user_id')) {
                $recapFilter = Session::get('filtre_pasttime.user_id');
                $q->where('past_times.user_id', '=', $recapFilter);
            } else {
                $q->where('past_times.user_id', '>', 0);
            }
            if (Session::has('filtre_pasttime.organisation_id')) {
                $recapFilter = Session::get('filtre_pasttime.organisation_id');
                $q->join('organisation_user', 'past_times.user_id', '=', 'organisation_user.user_id');
                $q->where('organisation_user.organisation_id', '=', $recapFilter);

            }
        } else {
            $recapFilter = Auth::id();
            $q->whereUserId(Auth::id());
        }
        $recap = PastTime::Recap($recapFilter, $date_filtre_start, $date_filtre_end);
        $pending_invoice_amount = 0;
        foreach ($recap as $recap_item) {
            $pending_invoice_amount += $recap_item->amount;
        }

        $params = array();
        $params['times'] = $q->orderBy('date_past', 'DESC')->with('location', 'location.city')->paginate($itemPerPage);
        $params['recap'] = $recap;
        $params['pending_invoice_amount'] = $pending_invoice_amount;

        $params = array_merge($params, Subscription::getActiveSubscriptionInfos());

        return View::make('pasttime.liste', $params);
    }

    public function add()
    {
        return View::make('pasttime.add');
    }

    public function add_check()
    {
        $validator = Validator::make(Input::all(), PastTime::$rules);
        if (!$validator->fails()) {
            $time = new PastTime;
            $date_past_explode = explode('/', Input::get('date_past'));
            $time->date_past = $date_past_explode[2] . '-' . $date_past_explode[1] . '-' . $date_past_explode[0];
            $dateTime_start = new DateTime($time->date_past);
            $time->time_start = $dateTime_start->format('Y-m-d') . ' ' . Input::get('time_start') . ':00';
            if (Input::get('time_end')) {
                if (Input::get('time_end') <= Input::get('time_start')) {
                    $dateTime_start->modify('+1 day');
                }
                $time->time_end = $dateTime_start->format('Y-m-d') . ' ' . Input::get('time_end') . ':00';
            }
            if (Auth::user()->isSuperAdmin()) {
                $time->user_id = Input::get('user_id');
                $time->organisation_id = Input::get('organisation_id');
                $time->invoice_id = Input::get('invoice_id');
                $time->is_free = Input::get('is_free', false);
            } else {
                $time->user_id = Auth::user()->id;
                $time->is_free = false;
            }
            $time->ressource_id = Input::get('ressource_id');
            $time->comment = Input::get('comment');
            $time->location_id = Input::get('location_id');

            if ($time->save()) {
                return Redirect::route('pasttime_list', $time->id)->with('mSuccess', 'Le temps passé a bien été ajouté');
            } else {
                return Redirect::route('pasttime_add')->with('mError', 'Impossible de créer ce temps passé')->withInput();
            }
        } else {
            return Redirect::route('pasttime_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function modify($id)
    {
        $time = $this->dataExist($id);

        return View::make('pasttime.modify', array('time' => $time));
    }

    public function modify_check($id)
    {
        $time = $this->dataExist($id);

        $validator = Validator::make(Input::all(), PastTime::$rules);
        if (!$validator->fails()) {
            $date_past_explode = explode('/', Input::get('date_past'));
            $time->date_past = $date_past_explode[2] . '-' . $date_past_explode[1] . '-' . $date_past_explode[0];
            $time->time_start = $time->date_past . ' ' . Input::get('time_start') . ':00';
            if (Input::get('time_end')) {
                $time->time_end = $time->date_past . ' ' . Input::get('time_end') . ':00';
            }
            if (Auth::user()->isSuperAdmin()) {
                $time->user_id = Input::get('user_id');
                $time->organisation_id = Input::get('organisation_id');
                $time->invoice_id = Input::get('invoice_id');
                $time->is_free = Input::get('is_free');
            } else {
                $time->user_id = Auth::user()->id;
            }
            $time->ressource_id = Input::get('ressource_id');
            $time->comment = Input::get('comment');
            $time->location_id = Input::get('location_id');

            if ($time->save()) {
                return Redirect::route('pasttime_list', $time->id)->with('mSuccess', 'Le temps passé a bien été modifié');
            } else {
                return Redirect::route('pasttime_modify', $time->id)->with('mError', 'Impossible de modifier ce temps passé')->withInput();
            }
        } else {
            return Redirect::route('pasttime_modify', $time->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function delete($id)
    {
        if (PastTime::destroy($id)) {
            return Redirect::route('pasttime_list')->with('mSuccess', 'Cette ligne a bien été supprimée');
        } else {
            return Redirect::route('pasttime_list')->with('mError', 'Impossible de supprimer cette ligne !');
        }
    }

    public function cancelFilter()
    {
        Session::forget('filtre_pasttime.user_id');
        Session::forget('filtre_pasttime.organisation_id');
        Session::forget('filtre_pasttime.start');
        Session::forget('filtre_pasttime.end');
        Session::forget('filtre_pasttime.filtre_toinvoice');
        return Redirect::route('pasttime_list');
    }

    public function invoice()
    {
        $items = PastTime::query()
            ->whereIn('id', Input::get('items'))
            ->where('invoice_id', 0)
            ->orderBy('ressource_id', 'ASC')
            ->orderBy('time_start', 'ASC')
            ->get();

        $lines = array();
        $ressources = array();
        $users = array();
        $organisations = array();
        $user = null;
        foreach ($items as $item) {
            $ressources[$item->ressource_id] = $item->ressource()->getResults();
            $lines[$item->ressource_id][] = $item;
            $users[$item->user_id] = true;
            $user = $item->user()->getResults();
            $organisation = $user->organisations->first();
            if (!$organisation) {
                $organisation = new Organisation();
                $organisation->name = implode(' ', array($user->firstname, $user->lastname));
                $organisation->country_id = Country::where('code', 'FR')->first()->id;
                $organisation->save();
                $user->organisations()->save($organisation);
            }
            $organisations[$organisation->id] = $organisation;
        }

        if (count($organisations) > 1) {
            return Redirect::route('pasttime_list')->with('mError', 'Impossible de générer la facture pour plusieurs sociétés à la fois');
        }

        if (count($users) == 0) {
            return Redirect::route('pasttime_list');
        }

        /** @var Organisation $organisation */
        $organisation = array_pop($organisations);

        $invoice = new Invoice();
        if ($organisation->accountant_id) {
            $invoice->user_id = $organisation->accountant_id;
        } else {
            $invoice->user_id = $user->id;
        }
        $invoice->created_at = new \DateTime();
        $invoice->organisation_id = $organisation->id;
        $invoice->type = 'F';
        $invoice->days = date('Ym');
        $invoice->number = $invoice->next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $organisation->fulladdress;
        $invoice->date_invoice = new \DateTime();
        $invoice->deadline = new \DateTime(date('Y-m-d', strtotime('+1 month')));
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();
        $vat = VatType::where('value', 20)->first();

        $orderIndex = 0;
        $invoice_lines = array();
        foreach ($lines as $ressource_id => $line) {
            $ressource = $ressources[$ressource_id];
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->amount = 0;
            $invoice_line->order_index = $orderIndex++;
            if ($ressource_id == Ressource::TYPE_COWORKING) {

                $items_per_user = array();
                foreach ($line as $item) {
                    if (!$item->is_free) {
                        if (!isset($items_per_user[$item->user->id])) {
                            $items_per_user[$item->user->id] = array();
                        }
                        $items_per_user[$item->user->id][] = $item;
                    }
                }
                $item_group = array();
                foreach ($items_per_user as $user_id => $sorted_items) {
                    $previous_item = null;
                    foreach ($sorted_items as $item) {
                        $is_valid = true;
                        if ($previous_item != null) {
                            if ($previous_item->date_past == $item->date_past) {
                                if ((strtotime($item->time_start) - strtotime($previous_item->time_end)) / 3600 <= self::COWORKING_HALF_DAY_MAX_DURATION) {
                                    $previous_item->time_end = $item->time_end;
                                    $item->delete();
                                    $is_valid = false;
                                }
                            }
                        }
                        if ($is_valid) {
                            $previous_item = $item;
                            $item_group[$user_id][] = $item;
                        }
                    }
                }
                foreach ($item_group as $user_id => $line_content) {
                    $invoice_line->text = 'Coworking';
                    $sum_duration = 0;

                    foreach ($line_content as $item) {
                        $duration = min(2, ceil(((strtotime($item->time_end) - strtotime($item->time_start)) / 3600) / self::COWORKING_HALF_DAY_MAX_DURATION));
                        $sum_duration += $duration;
                        $invoice_line->text .= sprintf("\n - %s de %s à %s (%s demi journée%s)", date('d/m/Y', strtotime($item->time_start)),
                            date('H:i', strtotime($item->time_start)), date('H:i', strtotime($item->time_end)), $duration, ($duration > 1) ? 's' : '');
                        if (count($users) > 1) {
                            $invoice_line->text .= ' - ' . $item->user()->getResults()->fullname;
                        }
                        $invoice_line->amount += $duration * (self::COWORKING_HALF_DAY_PRICING / 1.2);

                        $item->invoice_id = $invoice->id;
                        $item->save();
                    }
                    $invoice_line->text .= sprintf("\nTotal : %s demi journée%s\n\n", $sum_duration, ($sum_duration > 1) ? 's' : '');
                }
            } else {
                $invoice_line->text = sprintf('Location d\'espace de réunion - %s', $ressource->name);
                foreach ($line as $item) {
                    $invoice_line->text .= sprintf("\n - %s de %s à %s", date('d/m/Y', strtotime($item->time_start)), date('H:i', strtotime($item->time_start)), date('H:i', strtotime($item->time_end)));
                    $invoice_line->amount += min(7, (strtotime($item->time_end) - strtotime($item->time_start)) / 3600) * $ressource->amount;

                    $item->invoice_id = $invoice->id;
                    $item->save();
                }
            }
            $invoice_line->vat_types_id = $vat->id;
            $invoice_line->ressource_id = $item->ressource_id;
            $invoice_line->subscription_user_id = $invoice->user_id;

            $invoice_line->save();
            $invoice_lines[] = $invoice_line;
        }
        /*
                foreach ($organisation->invoicing_rules() as $rule) {
                    $processor = $rule->createProcessor();
                    if ($processor) {
                        $processor->execute($invoice_lines);
                    }
                }
        */
        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été générée');
    }

    public function confirm($id)
    {
        $time = $this->dataExist($id);
        if ($time->date_past != date('Y-m-d')) {
            $time->confirmed = true;
            $time->save();
            if (Request::ajax()) {
                return Response::make('<i class="fa fa-check"></i>', 200);
            }
        } else {
            if (Request::ajax()) {
                return Response::make('<i class="fa fa-times"></i>', 200);
            }
        }

        return Redirect::route('pasttime_list')->with('mSuccess', 'Cette entrée a été confirmée');
    }

    public function confirmMultiple()
    {
        if (count(Input::get('items')) == 0) {
            return Redirect::route('pasttime_list');
        }
        $items = PastTime::query()
            ->whereIn('id', Input::get('items'))
            ->get();

        foreach ($items as $item) {
            if ($item->date_past != date('Y-m-d')) {
                $item->confirmed = true;
                $item->save();
            }
        }

        return Redirect::route('pasttime_list')->with('mSuccess', 'Ces entrées ont étés confirmées');
    }

    public function gift()
    {
        if (count(Input::get('items')) == 0) {
            return Redirect::route('pasttime_list');
        }
        $items = PastTime::query()
            ->whereIn('id', Input::get('items'))
            ->get();

        foreach ($items as $item) {
            if ($item->date_past != date('Y-m-d')) {
                $item->is_free = true;
                $item->save();
            }
        }

        return Redirect::route('pasttime_list')->with('mSuccess', 'Ces entrées ont étés notées commeoffertes');
    }

    public function globalAction()
    {
        if (Input::has('invoice')) {
            return $this->invoice();
        }
        if (Input::has('confirm')) {
            return $this->confirmMultiple();
        }
        if (Input::has('gift')) {
            return $this->gift();
        }
    }
}