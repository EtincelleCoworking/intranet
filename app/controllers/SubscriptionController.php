<?php

class SubscriptionController extends BaseController
{
    public function cancelFilter()
    {
        Session::forget('filtre_subscription.user_id');
        Session::forget('filtre_subscription.organisation_id');
        Session::forget('filtre_subscription.city_id');
        return Redirect::route('subscription_list');
    }
    /**
     * List of vats
     */
    public function liste()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_city_id') && !empty(Input::get('filtre_city_id'))) {
                Session::put('filtre_subscription.city_id', Input::get('filtre_city_id'));
            } else {
                Session::forget('filtre_subscription.city_id');
            }
            if (Input::has('filtre_organisation_id')) {
                Session::put('filtre_subscription.organisation_id', Input::get('filtre_organisation_id'));
            } else {
                Session::forget('filtre_subscription.organisation_id');
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_subscription.user_id', Input::get('filtre_user_id'));
            } else {
                Session::forget('filtre_subscription.user_id');
            }

        }

        $companies = array();
        $q = Subscription::join('organisations', 'subscription.organisation_id', '=', 'organisations.id')
            ->orderBy('organisations.name', 'desc')
            ->groupBy('organisations.name')
            ->addSelect('organisations.id')
            ->addSelect('organisations.name')
            ->addSelect(DB::raw('count(subscription.id) as count'))
            ->where('renew_at', '<=', date('Y-m-t'))
            //->where('renew_at', '<', (new DateTime())->modify('+1 month')->format('Y-m-d'))
            ->having('count', '>', 1);
        foreach ($q->get() as $item) {
            $companies[$item->id] = array('name' => $item->name, 'count' => $item->count);
        }


        $subscriptions = Subscription::orderBy('renew_at', 'ASC')
            ->join('users', 'subscription.user_id', '=', 'users.id')
            ->join('locations', 'users.default_location_id', '=', 'locations.id')
            ->select('subscription.*')
            //->join('cities', 'locations.city_id', '=', 'cities.id')
        ;
        if (Session::has('filtre_subscription.user_id')) {
            $subscriptions->where('subscription.user_id', '=', Session::get('filtre_subscription.user_id'));
        }
        if (Session::has('filtre_subscription.organisation_id')) {
            $subscriptions->where('subscription.organisation_id', '=', Session::get('filtre_subscription.organisation_id'));
        }
        if (Session::has('filtre_subscription.city_id')) {
            $subscriptions->where('locations.city_id', '=', Session::get('filtre_subscription.city_id'));
        }

        return View::make('subscription.liste', array('subscriptions' => $subscriptions->paginate(15), 'companies' => $companies));
    }

    public function add()
    {
        return View::make('subscription.add');
    }

    protected function populate($subscription)
    {
        $date_explode = explode('/', Input::get('renew_at'));
        $subscription->user_id = Input::get('user_id');
        $subscription->organisation_id = Input::get('organisation_id');
        $subscription->subscription_kind_id = Input::get('subscription_kind_id');
        $subscription->renew_at = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
        //$subscription->duration = Input::get('duration');
    }

    /**
     * Add Vat check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Subscription::$rulesAdd);
        if (!$validator->fails()) {
            $subscription = new Subscription;
            $this->populate($subscription);

            if ($subscription->save()) {
                return Redirect::route('subscription_list')->with('mSuccess', 'L\'abonnement a été ajouté');
            } else {
                return Redirect::route('subscription_add')->with('mError', 'Impossible de créer cet abonnement')->withInput();
            }
        } else {
            return Redirect::route('subscription_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    private function dataExist($id)
    {
        $data = Subscription::find($id);
        if (!$data) {
            return Redirect::route('subscription_list')->with('mError', 'Cet abonnement est introuvable !');
        } else {
            return $data;
        }
    }

    /**
     * Modify vat
     */
    public function modify($id)
    {
        $subscription = $this->dataExist($id);

        return View::make('subscription.add', array('subscription' => $subscription));
    }

    public function modify_check($id)
    {
        $validator = Validator::make(Input::all(), Subscription::$rulesAdd);
        if (!$validator->fails()) {
            $subscription = $this->dataExist($id);
            $this->populate($subscription);

            if ($subscription->save()) {
                return Redirect::route('subscription_list')->with('mSuccess', 'L\'abonnement a été mis à jour');
            } else {
                return Redirect::route('subscription_modify', $subscription->id)->with('mError', 'Impossible de modifier cet abonnement')->withInput();
            }
        } else {
            return Redirect::route('subscription_modify')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }


    public function delete($id)
    {
        if (Subscription::destroy($id)) {
            return Redirect::route('subscription_list')->with('mSuccess', 'Cet abonnement a bien été supprimé');
        } else {
            return Redirect::route('subscription_list')->with('mError', 'Impossible de supprimer cet abonnement');
        }
    }

    public function renew($id)
    {
        $subscription = $this->dataExist($id);

        $invoice = new Invoice();
        $invoice->type = 'F';
        $invoice->user_id = $subscription->user_id;
        $invoice->organisation_id = $subscription->organisation_id;
        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $subscription->organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();

        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->ressource_id = $subscription->kind->ressource_id;
        $invoice_line->amount = $subscription->kind->price;
        $date = new \DateTime($subscription->renew_at);
        $date2 = new \DateTime($subscription->renew_at);
        $date2->modify('+' . $subscription->kind->duration);
        if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING) {
            $invoice_line->subscription_from = $date->format('Y-m-d');
            $invoice_line->subscription_to = $date2->format('Y-m-d');
            $invoice_line->subscription_hours_quota = $subscription->kind->hours_quota;
            $invoice_line->subscription_user_id = $subscription->user_id;
        }
        $date2->modify('-1 day');
        $invoice_line->text = sprintf("%s\nDu %s au %s", $subscription->formattedName(), $date->format('d/m/Y'), $date2->format('d/m/Y'));
        $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
        $invoice_line->order_index = 1;
        $invoice_line->save();

        if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING && $subscription->user->is_student) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = $subscription->kind->ressource_id;
            $invoice_line->amount = -0.2 * $subscription->kind->price;
            $invoice_line->text = 'Réduction commerciale étudiant (-20%)';
            $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
            $invoice_line->order_index = 2;
            $invoice_line->save();
        }

        $date = new DateTime($subscription->renew_at);
        $date->modify('+' . $subscription->kind->duration);
        $subscription->renew_at = $date->format('Y-m-d');
        $subscription->save();

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a été créée');

    }

    public function renewCompany($id)
    {
        $organisation = Organisation::find($id);
        if (!$organisation) {
            return Redirect::route('subscription_list')->with('mError', 'Société inconnue');
        }

        $subscriptions = Subscription::where('organisation_id', $id)
            ->join('subscription_kind', 'subscription_kind_id', '=', 'subscription_kind.id', 'left outer')
            ->orderBy('subscription_kind.price', 'DESC')
            ->orderBy('subscription.renew_at', 'ASC')
            ->where('subscription.renew_at', '<=', date('Y-m-t'))
            ->get();
        if (count($subscriptions) == 0) {
            return Redirect::route('subscription_list')->with('mError', 'Aucun abonnement pour cette société');
        }
        $invoice = new Invoice();
        $invoice->type = 'F';
        $invoice->user_id = $organisation->accountant_id;
        $invoice->organisation_id = $organisation->id;
        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();

        $skipped_first = false;
        $discountable_amount = 0;
        $student_amount = 0;
        $index = 1;
        foreach ($subscriptions as $subscription) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = $subscription->kind->ressource_id;
            $invoice_line->amount = $subscription->kind->price;
            if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING) {
                if (!$skipped_first) {
                    $skipped_first = true;
                } else {
                    $discountable_amount += $invoice_line->amount;
                }
            }
            $date = new \DateTime($subscription->renew_at);
            $date2 = new \DateTime($subscription->renew_at);
            $invoice_line->subscription_from = $date->format('Y-m-d');
            $date2->modify('+' . $subscription->kind->duration);
            if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING) {
                $invoice_line->subscription_to = $date2->format('Y-m-d');
                $invoice_line->subscription_hours_quota = $subscription->kind->hours_quota;
                $invoice_line->subscription_user_id = $subscription->user_id;
                if ($subscription->user->is_student) {
                    if ($skipped_first) {
                        $student_amount += $invoice_line->amount - 0.2 * $invoice_line->amount;
                    } else {
                        $student_amount += $invoice_line->amount;
                    }
                }
            }


            $date2->modify('-1 day');
            $caption = str_replace(array('%OrganisationName%', '%UserName%'), array($subscription->organisation->name, $subscription->user->fullname), $subscription->kind->name);
            $invoice_line->text = sprintf("%s\nDu %s au %s", $caption, $date->format('d/m/Y'), $date2->format('d/m/Y'));
            $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
            $invoice_line->order_index = $index++;
            $invoice_line->save();

            $date3 = new DateTime($subscription->renew_at);
            $date3->modify('next month');
            $subscription->renew_at = $date3->format('Y-m-d');
            $subscription->save();
        }
        if ($discountable_amount > 0) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->amount = -0.2 * $discountable_amount;
            $invoice_line->text = 'Réduction commerciale équipe (-20% à partir du 2ème collaborateur)';
            $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->order_index = $index++;
            $invoice_line->save();
        }

        if ($student_amount > 0) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->amount = -0.2 * $student_amount;
            $invoice_line->text = 'Réduction commerciale étudiant (-20%)';
            $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->order_index = $index++;
            $invoice_line->save();
        }

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a été créée');

    }

//    /**
//     * Modify vat (form)
//     */
//    public function modify_check($id)
//    {
//        $vat = $this->dataExist($id);
//
//        $validator = Validator::make(Input::all(), VatType::$rules);
//        if (!$validator->fails()) {
//            $vat->value = Input::get('value');
//            if ($vat->save()) {
//                return Redirect::route('vat_modify', $vat->id)->with('mSuccess', 'Cette vat a bien été modifiée');
//            } else {
//                return Redirect::route('vat_modify', $vat->id)->with('mError', 'Impossible de modifier cette vat')->withInput();
//            }
//        } else {
//            return Redirect::route('vat_modify', $vat->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
//        }
//    }


}