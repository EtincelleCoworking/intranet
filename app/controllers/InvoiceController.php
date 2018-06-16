<?php

/**
 * Invoice Controller
 */
class InvoiceController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id, $tpl)
    {
        if (Auth::user()->isSuperAdmin()) {
            $data = Invoice::find($id);
        } else {
            $data = Invoice::select('invoices.*')
                ->join('organisations', 'organisations.id', '=', 'invoices.organisation_id')
                ->where(function ($query) {
                    $query->where('organisations.accountant_id', Auth::id())
                        ->orWhere('invoices.user_id', Auth::id());
                })
                ->where('invoices.id', $id)->first();
        }

        if (!$data) {
            return Redirect::route($tpl)->with('mError', 'Cet élément est introuvable !');
        } else {
            return $data;
        }
    }

    public function cancelFilter()
    {
        Session::forget('filtre_invoice.user_id');
        Session::forget('filtre_invoice.organisation_id');
        Session::forget('filtre_invoice.location_id');
        Session::forget('filtre_invoice.start');
        Session::forget('filtre_invoice.end');
        Session::forget('filtre_invoice.filtre_unpaid');
        return Redirect::route('invoice_list');
    }

    public function invoiceList()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_location_id')) {
                Session::put('filtre_invoice.location_id', Input::get('filtre_location_id'));
            } else {
                Session::forget('filtre_invoice.location_id');
            }
            if (Input::has('filtre_organisation_id')) {
                Session::put('filtre_invoice.organisation_id', Input::get('filtre_organisation_id'));
            } else {
                Session::forget('filtre_invoice.organisation_id');
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_invoice.user_id', Input::get('filtre_user_id'));
            } else {
                Session::forget('filtre_invoice.user_id');
            }
            if (Input::has('filtre_start')) {
                $date_start_explode = explode('/', Input::get('filtre_start'));
                if (count($date_start_explode) == 3) {
                    Session::put('filtre_invoice.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
                } else {
                    Session::forget('filtre_invoice.start');
                }
            }
            if (Input::has('filtre_end')) {
                $date_end_explode = explode('/', Input::get('filtre_end'));
                if (count($date_end_explode) == 3) {
                    Session::put('filtre_invoice.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
                } else {
                    Session::forget('filtre_invoice.end');
                }
            } else {
                Session::put('filtre_invoice.end', date('Y-m-d'));
            }
            if (Input::has('filtre_unpaid')) {
                Session::put('filtre_invoice.filtre_unpaid', Input::get('filtre_unpaid'));
            } else {
                Session::put('filtre_invoice.filtre_unpaid', false);
            }
        }
        if (Session::has('filtre_invoice.start')) {
            $date_filtre_start = Session::get('filtre_invoice.start');
            $date_filtre_end = Session::get('filtre_invoice.end');
        } else {
            $date_filtre_start = null;
            $date_filtre_end = null;
        }

        $q = Invoice::InvoiceOnly();
        if ($date_filtre_start && $date_filtre_end) {
            $q->whereBetween('date_invoice', array($date_filtre_start, $date_filtre_end));
        }
        if (Session::get('filtre_invoice.location_id')) {
            $q->select('invoices.*');
            $q->distinct();
            $q->join('invoices_items', 'invoices.id', '=', 'invoices_items.invoice_id');
            $q->join('ressources', 'ressources.id', '=', 'invoices_items.ressource_id');
            $q->where('ressources.location_id', Session::get('filtre_invoice.location_id'));
        }
        if (Session::get('filtre_invoice.filtre_unpaid')) {
            $q->whereNull('date_payment');
        }
        if (Auth::user()->role == 'member') {
            $q->select('invoices.*');
            $q->join('organisations', 'organisations.id', '=', 'invoices.organisation_id');
            $q->where(function ($query) {
                $query->where('organisations.accountant_id', Auth::id())
                    ->orWhere('invoices.user_id', Auth::id());
            });
        } else {
            if (Session::has('filtre_invoice.user_id')) {
                $q->whereUserId(Session::get('filtre_invoice.user_id'));
            }
            if (Session::has('filtre_invoice.organisation_id')) {
                $q->whereOrganisationId(Session::get('filtre_invoice.organisation_id'));
            }
        }


        $q->orderBy('created_at', 'DESC');
        $q->with('user', 'organisation', 'items', 'items.vat');
//        if (Auth::user()->role != 'superadmin') {
//            $q->whereUserId(Auth::user()->id);
        //      }
        $invoices = $q->paginate(15);

        return View::make('invoice.liste', array('invoices' => $invoices));
    }

    public function quoteList($filtre)
    {
        $q = Invoice::QuoteOnly($filtre)->orderBy('created_at', 'DESC');
        if (!Auth::user()->isSuperAdmin()) {
            $q->whereUserId(Auth::user()->id);
        }
        $invoices = $q->paginate(15);

        return View::make('invoice.quote_list', array('invoices' => $invoices, 'filtre' => $filtre));
    }

    /**
     * Modify invoice
     */
    public function modify($id)
    {
        $template = 'invoice.modify';

        $invoice = $this->dataExist($id, $template);

        if (!$invoice) {
            return Redirect::route('invoice_list')->with('mError', 'Cette facture est introuvable !');
        }

        $date_explode = explode('-', $invoice->date_invoice);
        $dead_explode = explode('-', $invoice->deadline);
        if ($invoice->date_payment) {
            $payment_explode = explode('-', $invoice->date_payment);
        } else {
            $payment_explode = array(date('Y'), date('m'), date('d'));
        }

        return View::make($template, array('invoice' => $invoice, 'date_explode' => $date_explode, 'dead_explode' => $dead_explode, 'payment_explode' => $payment_explode));
    }

    /**
     * Modify invoice (form)
     */
    public function modify_check($id)
    {
        $invoice = $this->dataExist($id, 'invoice_list');

        $validator = Validator::make(Input::all(), Invoice::$rules);
        if (!$validator->fails()) {
            $date_invoice_explode = explode('/', Input::get('date_invoice'));
            $invoice->date_invoice = $date_invoice_explode[2] . '-' . $date_invoice_explode[1] . '-' . $date_invoice_explode[0];
            $date_deadline_explode = explode('/', Input::get('deadline'));
            $invoice->deadline = $date_deadline_explode[2] . '-' . $date_deadline_explode[1] . '-' . $date_deadline_explode[0];

            if (Input::get('expected_payment_at')) {
                $date_payment_explode = explode('/', Input::get('expected_payment_at'));
                $invoice->expected_payment_at = $date_payment_explode[2] . '-' . $date_payment_explode[1] . '-' . $date_payment_explode[0];
            } else {
                $invoice->expected_payment_at = null;
            }
            if (Input::get('date_payment')) {
                $date_payment_explode = explode('/', Input::get('date_payment'));
                $invoice->date_payment = $date_payment_explode[2] . '-' . $date_payment_explode[1] . '-' . $date_payment_explode[0];
            } else {
                $invoice->date_payment = null;
            }
            if (Input::get('sent_at')) {
                $sent_at_explode = explode('/', Input::get('sent_at'));
                $invoice->sent_at = $sent_at_explode[2] . '-' . $sent_at_explode[1] . '-' . $sent_at_explode[0];
            } else {
                $invoice->sent_at = null;
            }
            $invoice->address = Input::get('address');
            $invoice->business_terms = Input::get('business_terms');
            $invoice->details = Input::get('details');
            $invoice->on_hold = Input::get('on_hold');
            $invoice->is_lost = Input::get('is_lost');
            $invoice->user_id = Input::get('user_id');
            $invoice->organisation_id = Input::get('organisation_id');

            if ($invoice->save()) {
                $feedback_message = 'La facture a bien été modifiée';
                if (!$invoice->sent_at) {
                    $feedback_message .= sprintf('<p><a href="%s" class="btn btn-success">Envoyer</a></p>', route('invoice_send', array('invoice_id' => $invoice->id)));
                }
                return Redirect::route('invoice_list', $invoice->id)->with('mSuccess', $feedback_message);
            } else {
                return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
            }
        } else {
            return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add invoice
     */
    public function add($type, $organisation = null)
    {
        if ($organisation) {
            return View::make('invoice.add_organisation', array('organisation' => $organisation, 'type' => $type));
        } else {
            $last_organisation_id = Input::old('organisation_id');
            return View::make('invoice.add', array('last_organisation_id' => $last_organisation_id, 'type' => $type));
        }
    }

    /**
     * Add Invoice check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Invoice::$rulesAdd);
        if (!$validator->fails() and (!Input::get('organisation_id') or Input::get('user_id'))) {
            $date_explode = explode('/', Input::get('date_invoice'));
            $days = $date_explode[2] . $date_explode[1];

            $invoice = new Invoice;
            $invoice->user_id = Input::get('user_id');
            $invoice->organisation_id = Input::get('organisation_id');
            $invoice->type = Input::get('type');
            $invoice->days = $days;
            $invoice->date_invoice = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
            $invoice->number = Invoice::next_invoice_number(Input::get('type'), $days);
            $invoice->address = Input::get('address');
            $location = Location::orderBy('id', 'asc')->where('enabled', '=', true)->first();
            if ($location) {
                $invoice->business_terms = $location->default_business_terms;
            }

            $invoice->on_hold = false;

            $date = new DateTime($invoice->date_invoice);
            $date->modify('+1 month');
            $invoice->deadline = $date->format('Y-m-d');
            $invoice->expected_payment_at = $invoice->deadline;


            if ($invoice->save()) {
                return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été ajoutée');
            } else {
                return Redirect::route('invoice_add', Input::get('type'))->with('mError', 'Impossible de créer cette facture')->withInput();
            }
        } else {
            return Redirect::route('invoice_add', Input::get('type'))->with('mError', 'Il y a des erreurs')->withInput()->withErrors($validator->messages());
        }
    }

    /**
     * Validate a quotation
     */
    public function validate($id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->dataExist($id, 'invoice_list');

        $invoice_comment = new InvoiceComment();
        $invoice_comment->invoice_id = $invoice->id;
        $invoice_comment->user_id = Auth::user()->id;
        $invoice_comment->content = sprintf('Devis %s validé', $invoice->ident);
        $invoice_comment->save();

        $invoice->created_at = new \DateTime();
        $invoice->type = 'F';
        $invoice->days = date('Ym');
        $invoice->number = Invoice::next_invoice_number('F', $invoice->days);
        $invoice->date_invoice = new DateTime();

        $date = clone $invoice->date_invoice;
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');

        if ($invoice->save()) {
            return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été générée');
        } else {
            return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Impossible de générer la facture');
        }
    }

    /**
     * Cancel a quotation
     */
    public function cancel($id)
    {
        $invoice = $this->dataExist($id, 'invoice_list');

        $invoice->date_canceled = date('Y-m-d');

        if ($invoice->save()) {
            return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'Le devis a bien été refusé');
        } else {
            return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Impossible de refuser le devis');
        }
    }

    /**
     * Delete a quotation
     */
    public function delete($id)
    {
        if (InvoiceItem::where('invoice_id', '=', $id)->delete()) {
            if (Invoice::destroy($id)) {
                return Redirect::route('invoice_list')->with('mSuccess', 'Le devis a bien été supprimé');
            } else {
                return Redirect::route('invoice_modify', $id)->with('mError', 'Impossible de supprimer ce devis');
            }
        } else {
            return Redirect::route('invoice_modify', $id)->with('mError', 'Impossible de supprimer ce devis');
        }
    }

    /**
     * Print invoice to PDF
     */
    public function print_pdf($id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->dataExist($id, 'invoice_list');


        //echo $invoice->getPdfHtml();exit;

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadHTML($invoice->getPdfHtml());
        return $pdf->stream($invoice->ident . '.pdf');
    }

    public function stripe($id)
    {
        $invoice = $this->dataExist($id, 'invoice_list');

        // Set your secret key: remember to change this to your live secret key in production
// See your keys here https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($_ENV['stripe_sk']);

// Get the credit card details submitted by the form
        $stripeToken = Request::input('stripeToken');

// Create the charge on Stripe's servers - this will charge the user's card
        try {
            $amount = Invoice::TotalInvoiceWithTaxes($invoice->items) * 100;
            if ($amount) {
                $charge = \Stripe\Charge::create(array(
                        "amount" => $amount, // amount in cents, again
                        "currency" => "eur",
                        "source" => $stripeToken,
                        "description" => "Facture " . $invoice->ident)
                );
            }
            $invoice->date_payment = date('Y-m-d');
            $invoice->save();

            $invoice_comment = new InvoiceComment();
            $invoice_comment->invoice_id = $invoice->id;
            $invoice_comment->user_id = Auth::user()->id;
            $invoice_comment->content = 'Payé par CB avec Stripe';
            $invoice_comment->save();

            return Redirect::route('invoice_list')
                ->with('mSuccess', sprintf('La facture %s a été payée', $invoice->ident));

        } catch (\Stripe\Error\Card $e) {
            // The card has been declined
        }
    }

    public function send($invoice_id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->dataExist($invoice_id, 'invoice_list');
        $invoice->send();

        return Redirect::route('invoice_list')
            ->with('mSuccess', sprintf('La facture %s a été envoyée par email à %s', $invoice->ident, $to));
    }


    public function paid($invoice_id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->dataExist($invoice_id, 'invoice_list');

        $invoice->date_payment = date('Y-m-d');
        $invoice->save();

        return Redirect::route('cashflow')
            ->with('mSuccess', sprintf('La facture %s a été notée comme payée', $invoice->ident));
    }


    public function unpaid($space_slug = null)
    {
        if ($space_slug) {
#             left outer join `ressources` on invoices_items.`ressource_id` = `ressources`.`id`
# left outer join `locations` on ressources.`location_id` = `locations`.`id`

            $from = '

join users u on u.id = invoices_items.subscription_user_id 
join `locations` on u.default_location_id = `locations`.`id` 

';

            $where = sprintf(' AND locations.slug = "%s" ', $space_slug);
        } else {
            $from = '';
            $where = '';
        }


        $sql = 'select 
invoices.organisation_id, 
organisations.name,
organisations.last_invoice_reminder_at,
count(distinct(invoices.id)) as nb_invoices,
sum(invoices_items.amount) as total_ht,
sum(invoices_items.amount * (1+vat_types.value/100)) as total_ttc,
min(invoices.date_invoice) as older_invoice_at

from invoices_items 
join invoices on invoices.id = invoices_items.invoice_id
join organisations on invoices.organisation_id = organisations.id
join vat_types ON invoices_items.vat_types_id = vat_types.id
' . $from . '
WHERE invoices.type = \'F\' 
  AND invoices.date_payment IS NULL
  AND invoices.on_hold = 0
  AND invoices.is_lost = 0
  ' . $where . '
GROUP by invoices.organisation_id
order by older_invoice_at ASC';

        $items = DB::select(DB::raw($sql));
        return View::make('invoice.unpaid', array(
            'items' => $items,
            'space_slug' => $space_slug,
            'locations' => Location::orderBy('name', 'asc')->get(),
        ));

    }


    public function invoicing()
    {
        $period_start = date('Y-m-01');
        $period_start = date('Y-m-d', Config::get('etincelle.activity_period_started'));
        $period_end = date('Y-m-t');
        $items = Organisation::join('past_times', 'past_times.organisation_id', '=', 'organisations.id')
            ->where('past_times.invoice_id', '=', 0)
            ->where('past_times.is_free', '=', 0)
            ->whereBetween('past_times.date_past', array($period_start, $period_end))
            ->where('past_times.ressource_id', '<>', Ressource::TYPE_COWORKING)
            ->select('organisations.*')
            ->groupBy('organisations.id')
            ->get();


        return View::make('invoice.invoicing', array(
            'items' => $items,
            'period_start' => $period_start,
            'period_end' => $period_end,
        ));
    }


}
