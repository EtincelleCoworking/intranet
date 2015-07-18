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
        if (Auth::user()->role == 'superadmin') {
            $data = Invoice::find($id);
        } else {
            $data = Invoice::whereUserId(Auth::user()->id)->find($id);
        }

        if (!$data) {
            return Redirect::route($tpl)->with('mError', 'Cet élément est introuvable !');
        } else {
            return $data;
        }
    }

    public function invoiceList()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_invoice.user_id', Input::get('filtre_user_id'));
            }
            if (Input::has('filtre_start')) {
                $date_start_explode = explode('/', Input::get('filtre_start'));
                Session::put('filtre_invoice.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
                if (!Input::has('filtre_user_id')) {
                    Session::forget('filtre_invoice.user_id');
                }
            }
            if (Input::has('filtre_end')) {
                $date_end_explode = explode('/', Input::get('filtre_end'));
                Session::put('filtre_invoice.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
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

//        if (Session::has('filtre_invoice.month')) {
//            $date_filtre_start = Session::get('filtre_invoice.year').'-'.Session::get('filtre_invoice.month').'-01';
//            $date_filtre_end = Session::get('filtre_invoice.year').'-'.Session::get('filtre_invoice.month').'-'.date('t', Session::get('filtre_invoice.month'));
//        } else {
//            $date_filtre_start = date('Y-m').'-01';
//            $date_filtre_end = date('Y-m').'-'.date('t', Session::get('filtre_invoice.month'));
//        }

        $q = Invoice::InvoiceOnly();
        if ($date_filtre_start && $date_filtre_end) {
            $q->whereBetween('date_invoice', array($date_filtre_start, $date_filtre_end));
        }
        if (Session::get('filtre_invoice.filtre_unpaid')) {
            $q->whereNull('date_payment');
        }
        if (Auth::user()->role == 'member') {
            $recapFilter = Auth::user()->id;
            $q->whereUserId(Auth::user()->id);
        } else {
            if (Session::has('filtre_invoice.user_id')) {
                $recapFilter = Session::get('filtre_invoice.user_id');
                $q->whereUserId($recapFilter);
            }
        }


        $q->orderBy('created_at', 'DESC');
        if (Auth::user()->role != 'superadmin') {
            $q->whereUserId(Auth::user()->id);
        }
        $invoices = $q->paginate(15);

        return View::make('invoice.liste', array('invoices' => $invoices));
    }

    public function quoteList($filtre)
    {
        $q = Invoice::QuoteOnly($filtre)->orderBy('created_at', 'DESC');
        if (Auth::user()->role != 'superadmin') {
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
        if (Auth::user()->role == 'superadmin') {
            $template = 'invoice.modify';
        } else {
            $template = 'invoice.show';
        }

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
            if (Input::get('date_payment')) {
                $date_payment_explode = explode('/', Input::get('date_payment'));
                $invoice->date_payment = $date_payment_explode[2] . '-' . $date_payment_explode[1] . '-' . $date_payment_explode[0];
            } else {
                $invoice->date_payment = null;
            }
            $invoice->address = Input::get('address');
            $invoice->details = Input::get('details');

            if ($invoice->save()) {
                return Redirect::route('invoice_list', $invoice->id)->with('mSuccess', 'La facture a bien été modifiée');
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
        if (!$validator->fails()) {
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

            $date = new DateTime($invoice->date_invoice);
            $date->modify('+1 month');
            $invoice->deadline = $date->format('Y-m-d');

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

        $invoice->type = 'F';
        $invoice->days = sprintf('Ym');
        $invoice->number = Invoice::next_invoice_number('F', $invoice->days);
        $invoice->date_invoice = new DateTime();

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
        $invoice = $this->dataExist($id, 'invoice_list');

        $snappy = App::make('snappy.pdf');

        $html = '
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
                <title>' . $invoice->ident . '</title>
            </head>
            <body>
                <table style="font-size:12px; width:100%">
                    <tbody>
                        <tr>
                            <td style="width:50%">
                                <strong>' . $_ENV['organisation_name'] . ' ' . $_ENV['organisation_status'] . '</strong><br />
                                ' . $_ENV['organisation_address'] . '<br />
                                ' . $_ENV['organisation_zipcode'] . ' ' . $_ENV['organisation_city'] . '<br />
                                ' . $_ENV['organisation_country'] . '<br />
                                <br />
                                SIRET : ' . $_ENV['organisation_siret'] . '<br />
                                TVA Intracommunautaire : ' . $_ENV['organisation_tva'] . '<br />
                                ' . $_ENV['organisation_status'] . ' au capital de ' . $_ENV['organisation_capital'] . '
                            </td>
                            <td stle="width:50%;" valign="top">
                                <div style="border:1px solid #666; border-radius: 6px; -moz-border-radius: 6px; background-color: #ccc; vertical-align: middle; text-align: center; width: 205px; height: 20px; padding-top:4px; margin-left:130px;">' . (($invoice->type == 'F') ? 'Facture' : 'Devis') . ' en € n° ' . $invoice->ident . '</div>
                                <div style="margin-top:5px; margin-left:130px; font-size:10px; text-align: right;">Le ' . date('d/m/Y', strtotime($invoice->date_invoice)) . '</div>
                                <div style="margin-left:130px; margin-top:10px;">
                                    ' . nl2br($invoice->address) . '
                                </div>
                            </td>
                        </tr>
                        ' . (($invoice->details) ? '
                        <tr>
                          <td colspan="2">
                            ' . $invoice->details . '
                          </td>
                        </tr>
                        ' : '') . '
                        <tr>
                            <td colspan="2">
                                <div style="margin-top:20px">
                                    <table cellpading="0" cellspacing="0" style="font-size:11px; width:100%; border:1px solid #666;">
                                        <thead>
                                            <tr>
                                                <th style="width:500px;">DESIGNATION</th>
                                                <th style="border-left:1px solid #666">TVA</th>
                                                <th style="border-left:1px solid #666">MONTANT HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
        $vats = array();
        $vat_total = array(
            'ht' => 0,
            'vat' => 0
        );
        foreach ($invoice->items as $item) {
            if (!array_key_exists($item->vat->id, $vats)) {
                $vats[$item->vat->id] = array(
                    'base' => 0,
                    'montant' => 0,
                    'taux' => $item->vat->value
                );
            }
            $vats[$item->vat->id]['base'] += $item->amount;
            $calc_vat = round((($item->amount * $item->vat->value) / 100), 2);
            $vats[$item->vat->id]['montant'] += $calc_vat;
            $vat_total['ht'] += $item->amount;
            $vat_total['vat'] += $calc_vat;

            $html .= '
                                            <tr valign="top">
                                                <td style="border-top:1px solid #666; padding:5px">' . nl2br($item->text) . '</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">' . $item->vat->value . '%</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">' . sprintf('%0.2f', $item->amount) . '€</td>
                                            </tr>
                                            ';
        }
        $html .= '
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div>
                                    <div>&nbsp;</div>
                                    <table style="font-size:12px; width:100%">
                                        <tbody>
                                            <tr>
                                                <td width="70%" valign="top">';
        if (count($vats) > 1) {
            $html .= '
                                                    <table cellpading="0" cellspacing="0" style="font-size:11px; width:60%; border-radius: 6px; -moz-border-radius: 6px; border: 1px solid #666; padding:5px;">
                                                        <thead>
                                                            <tr>
                                                                <th style="border-bottom:1px solid #666">% TVA</th>
                                                                <th style="border-bottom:1px solid #666">Base HT</th>
                                                                <th style="border-bottom:1px solid #666">TVA</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
            foreach ($vats as $vat) {
                $html .= '
                                                                <tr>
                                                                    <td style="text-align:right">' . sprintf('%0.2f', $vat['taux']) . '%</td>
                                                                    <td style="text-align:right">' . sprintf('%0.2f', $vat['base']) . '€</td>
                                                                    <td style="text-align:right">' . sprintf('%0.2f', $vat['montant']) . '€</td>
                                                                </tr>
                                                            ';
            }
            $html .= '
                                                        </tbody>
                                                    </table>';
        }
        $html .= '
                                                </td>
                                                <td valign="top" style="text-align:left;">
                                                    <table cellpading="0" cellspacing="0" style="font-size:11px; width:100%; border-radius: 6px; -moz-border-radius: 6px; border: 1px solid #666; padding:5px;">
                                                        <tbody>
                                                            <tr>
                                                                <th style="width: 60%; text-align:left; border-right:1px solid #666;">Total HT</th>
                                                                <td style="padding-left:5px; text-align:right; border-bottom:1px dashed #666">' . sprintf('%0.2f', $vat_total['ht']) . '€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left; border-right:1px solid #666;">Montant TVA</th>
                                                                <td style="padding-left:5px; text-align:right; border-bottom:1px dashed #666">' . sprintf('%0.2f', $vat_total['vat']) . '€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left; border-right:1px solid #666;">Total TTC</th>
                                                                <td style="padding-left:5px; text-align:right;">' . sprintf('%0.2f', ($vat_total['ht'] + $vat_total['vat'])) . '€</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                ' . (($invoice->type == 'F') ? (($invoice->date_payment) ? 'Facture payée le ' . date('d/m/Y', strtotime($invoice->date_payment)) : 'Cette facture est payable à réception de facture') : 'Ce devis est valide jusqu\'au ' . date('d/m/Y', strtotime($invoice->deadline))) . '
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="position:absolute; bottom:0; width:100%;">
                    <table cellpading="0" cellspacing="0" style="width:100%">
                        <tr>
                            <td style="width:45%" valign="top">
                                <table cellpading="0" cellspacing="0" style="width:98%; font-size:11px; border-radius: 6px; -moz-border-radius: 6px; border: 1px solid #666; padding:5px;">
                                    <thead>
                                        <tr>
                                            <th colspan="4" style="text-transform:uppercase">Relevé d\'Identité Bancaire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="font-size:10px;">
                                            <th style="text-align:left">Banque</th>
                                            <th style="text-align:left">Guichet</th>
                                            <th style="text-align:left">N° de compte</th>
                                            <th style="text-align:left">Clé</th>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <td>' . $_ENV['rib_bank'] . '</td>
                                            <td>' . $_ENV['rib_desk'] . '</td>
                                            <td>' . $_ENV['rib_account'] . '</td>
                                            <td>' . $_ENV['rib_key'] . '</td>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <th colspan="3" style="text-align:left">IBAN</th>
                                            <th style="text-align:left">BIC</th>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <td colspan="3">' . $_ENV['rib_iban'] . '</td>
                                            <td>' . $_ENV['rib_bic'] . '</td>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <th colspan="4">Domiciliation</th>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <td colspan="4">' . $_ENV['rib_domiciliation'] . '</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="width:55%; font-size:8px;">
                                <div style="width:95%">
                                    <div style="font-weight:bold">En conformité de l’article L 441-6 du Code de commerce :</div>
                                    <ul>
                                        <li>Pas d’escompte pour paiement anticipé.</li>
                                        <li>Tout règlement effectué après expiration de ce délai donnera lieu, à titre de pénalité de retard, à l\'application d’un intérêt égal à celui appliqué par la Banque Centrale Européenne à son opération de refinancement la plus récente, majoré de 10 points de pourcentage, ainsi qu\'à une indemnité forfaitaire pour frais de recouvrement d\'un montant de 40 Euros.</li>
                                        <li>Les pénalités de retard sont exigibles sans qu’un rappel soit nécessaire.</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </body>
        </html>';

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->stream($invoice->ident . '.pdf');;
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
}
