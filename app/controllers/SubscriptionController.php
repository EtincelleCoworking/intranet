<?php

class SubscriptionController extends BaseController
{
    /**
     * List of vats
     */
    public function liste()
    {
        $subscriptions = Subscription::orderBy('renew_at', 'ASC')
            ->paginate(15);

        return View::make('subscription.liste', array('subscriptions' => $subscriptions));
    }

    public function add()
    {
        return View::make('subscription.add');
    }

    protected function populate($subscription){
            $date_explode = explode('/', Input::get('renew_at'));

            $subscription->user_id = Input::get('user_id');
            $subscription->organisation_id = Input::get('organisation_id');
            $subscription->caption = Input::get('caption');
            $subscription->renew_at = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];
            $subscription->duration = Input::get('duration');
            $subscription->amount = Input::get('amount');
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

    public function modify_check($id){
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
        $invoice->address =$subscription->organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->save();

        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->amount = $subscription->amount;
        $date = new \DateTime($subscription->renew_at);
        $date2 = new \DateTime($subscription->renew_at);
        $date2->modify('next month');
        $date2->modify('-1 day');
        $invoice_line->text = sprintf("%s\nDu %s au %s", $subscription->caption,
            $date->format('d/m/Y'), $date2->format('d/m/Y'));
        $invoice_line->vat_types_id = 1;
        $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
        $invoice_line->save();
        $invoice_line->order_index = 1;

        $date = new DateTime($subscription->renew_at);
        $date->modify('+1 month');
        $subscription->renew_at = $date->format('Y-m-d');
        $subscription->save();

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