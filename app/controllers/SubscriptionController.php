<?php

class SubscriptionController extends BaseController
{
    /**
     * List of vats
     */
    public function liste()
    {


        $items = Subscription::join('organisations', 'subscription.organisation_id', '=', 'organisations.id')
            ->orderBy('organisations.name', 'desc')
            ->groupBy('organisations.name')
            ->addSelect('organisations.id')
            ->addSelect('organisations.name')
            ->addSelect(DB::raw('count(subscription.id) as count'))
            ->having('count', '>', 1)
            ->get();
        $companies = array();
        foreach ($items as $item) {
            $companies[$item->id] = array('name' => $item->name, 'count' => $item->count);
        }


        $subscriptions = Subscription::orderBy('renew_at', 'ASC')
            ->paginate(15);

        return View::make('subscription.liste', array('subscriptions' => $subscriptions, 'companies' => $companies));
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
        $subscription->duration = Input::get('duration');
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
        $invoice->save();

        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->amount = $subscription->kind->price;
        $date = new \DateTime($subscription->renew_at);
        $date2 = new \DateTime($subscription->renew_at);
        $invoice_line->subscription_from = $date->format('Y-m-d');
        $date2->modify('next month');
        $invoice_line->subscription_to = $date2->format('Y-m-d');
        $invoice_line->subscription_hours_quota = $subscription->kind->hours_quota;
        $invoice_line->subscription_user_id = $subscription->user_id;

        // update invoices_items set subscription_to = date_add(subscription_from, interval 1 MONTH) where subscription_from <> '0000-00-00 00:00:00'


        $date2->modify('-1 day');
        $invoice_line->text = sprintf("%s - %s\nDu %s au %s", $subscription->kind->name, $subscription->user->fullname,
            $date->format('d/m/Y'), $date2->format('d/m/Y'));
        $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->save();
        $invoice_line->order_index = 1;

        $date = new DateTime($subscription->renew_at);
        $date->modify('+1 month');
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
        $invoice->save();

        $skipped_first = false;
        $discountable_amount = 0;
        $index = 1;
        foreach ($subscriptions as $subscription) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->amount = $subscription->kind->price;
            if (!$skipped_first) {
                $skipped_first = true;
            } else {
                $discountable_amount += $invoice_line->amount;
            }
            $date = new \DateTime($subscription->renew_at);
            $date2 = new \DateTime($subscription->renew_at);
            $invoice_line->subscription_from = $date->format('Y-m-d');
            $date2->modify('next month');
            $invoice_line->subscription_to = $date2->format('Y-m-d');
            $invoice_line->subscription_hours_quota = $subscription->kind->hours_quota;
            $invoice_line->subscription_user_id = $subscription->user_id;

            $date2->modify('-1 day');
            $invoice_line->text = sprintf("%s - %s\nDu %s au %s", $subscription->kind->name, $subscription->user->fullname,
                $date->format('d/m/Y'), $date2->format('d/m/Y'));
            $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->order_index = $index++;
            $invoice_line->save();

            $date = new DateTime($subscription->renew_at);
            $date->modify('+1 month');
            $subscription->renew_at = $date->format('Y-m-d');
            $subscription->save();
        }

        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->amount = -0.2 * $discountable_amount;
        $invoice_line->text = 'Réduction commerciale équipe (-20% à partir du 2ème collaborateur)';
        $invoice_line->vat_types_id = VatType::whereValue(20)->first()->id;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->order_index = $index++;
        $invoice_line->save();

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