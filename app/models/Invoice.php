<?php

/**
 * Invoice Model
 */
class Invoice extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    public function scopeInvoiceOnly($query)
    {
        return $query->whereType('F')//->whereNull('date_canceled')
            ;
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNull('date_payment');
    }

    public function scopeQuoteOnly($query, $filtre = '')
    {
        if ($filtre == 'canceled') {
            return $query->whereType('D')->whereNotNull('date_canceled');
        } else {
            return $query->whereType('D')->whereNull('date_canceled');
        }
    }

    public function scopeInvoicesDesc($query, $user)
    {
        return $query->where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
    }

    public function scopeQuoteCanceled($query)
    {
        return $query->whereNotNull('date_canceled');
    }

    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Relation BelongsTo (Invoices belongs to Organisation)
     */
    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    /**
     * Relation One To Many (Invoice has many Invoices_Items)
     */
    public function items()
    {
        return $this->hasMany('InvoiceItem')->orderBy('order_index', 'ASC');;
    }

    /**
     * Relation One To Many (Invoice has many Invoices_Items)
     */
    public function comments()
    {
        return $this->hasMany('InvoiceComment');
    }

    /**
     * Identifier invoice
     */
    public function getIdentAttribute()
    {
        return $this->type . $this->days . '-' . str_pad($this->number, 4, 0, STR_PAD_LEFT);
    }

    /**
     * Identifier invoice
     */
    public function getCaptionAttribute()
    {
        if ($this->user) {
            return $this->ident . ' ' . $this->user->fullname;
        }
        return $this->ident;
    }

    /**
     * Days before deadline
     */
    public function getDaysDeadlineAttribute()
    {
        if ($this->deadline >= date('Y-m-d')) {
            $date1 = new DateTime($this->deadline);
            $date2 = new DateTime();
            $diff = $date2->diff($date1);

            return $diff->days;
        } else {
            return -1;
        }
    }

    /**
     * Total
     */
    public function getTotalAttribute()
    {
        $total = 0;

        if ($this->items) {
            /** @var InvoiceItem $value */
            foreach ($this->items as $key => $value) {
                $total += $value->amount;
            }
        }

        return sprintf('%0.2f', $total);
    }

    public function getTotalWithTaxesAttribute()
    {
        $total = 0;

        if ($this->items) {
            $rates = array();
            /** @var InvoiceItem $value */
            foreach ($this->items as $key => $value) {
                if (!isset($rates[$value->vat->value])) {
                    $rates[$value->vat->value] = 0;
                }
                $rates[$value->vat->value] += $value->amount;
            }
            foreach ($rates as $rate => $amount) {
                $total += $amount * (1 + $rate / 100);
            }
        }

        return sprintf('%0.2f', $total);
    }

    public function getStripeFeesAttribute()
    {
        $total = 0;

        /** @var InvoiceItem $value */
        foreach ($this->items as $key => $value) {
            $total += $value->amount * (1 + $value->vat->value / 100);
        }

        return sprintf('%0.2f', $total ? 0.25 + 1.8 / 100 * $total : 0);
    }

    /**
     * Total amount
     */
    public function scopeTotalInvoice($query, $items)
    {
        $total = 0;

        if ($items) {
            foreach ($items as $key => $value) {
                $total += $value->amount;
            }
        }

        return sprintf('%0.2f', $total);
    }

    /**
     * Total amount
     */
    public function scopeTotalInvoiceWithTaxes($query, $items)
    {
        $total = 0;

        if ($items) {
            foreach ($items as $key => $value) {
                $total += $value->amount * (1 + $value->vat->value / 100);
            }
        }

        return sprintf('%0.2f', $total);
    }

    /**
     * Get next invoice number
     */
    static public function next_invoice_number($type, $days)
    {
        $last = Invoice::where('type', $type)->where('days', $days)->orderBy('number', 'DESC')->first();
        if ($last) {
            return ($last->number + 1);
        } else {
            return 1;
        }
    }

    /**
     * Rules
     */
    public static $rules = array(
        'date_invoice' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'address' => 'required|min:1'
    );

    public function scopeSelectAll($query, $title = "Select", $user_id = null)
    {
//        var_dump($title);
//        var_dump($user_id);
        $selectVals[''] = $title;
        //$query = $this;
        if ($user_id) {
            $query->select('invoices.*');
            $query->join('organisation_user', 'invoices.organisation_id', '=', 'organisation_user.organisation_id');
            $query->where('organisation_user.user_id', $user_id);
        }
        $query = $query->orderBy('days', 'desc')->orderBy('number', 'desc')->where('type', 'F');
        $selectVals += $query->with('user')->get()->lists('caption', 'id');
        return $selectVals;
    }

    public function getPdfHtml()
    {
        $html = '
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
                <title>' . $this->ident . '</title>
            </head>
            <body>
                <table style="font-size:12px; width:100%">
                    <tbody>
                        <tr>
                            <td style="width:50%">
                                <strong>' . $_ENV['organisation_name'] . ' ' . $_ENV['organisation_status'] . '</strong><br />
                                ' . $_ENV['organisation_address'] . '<br />
                                ' . $_ENV['organisation_zipcode'] . ' ' . $_ENV['organisation_city'] . '<br />
                                ' . $_ENV['organisation_country'] . '<br />';
        if (!empty($_ENV['organisation_siret'])) {
            $html .= '<br />SIRET : ' . $_ENV['organisation_siret'];
        }
        if (!empty($_ENV['organisation_tva'])) {
            $html .= '<br />TVA Intracommunautaire : ' . $_ENV['organisation_tva'];
        }
        if (!empty($_ENV['organisation_status']) && !empty($_ENV['organisation_capital'])) {
            $html .= '<br />' . $_ENV['organisation_status'] . ' au capital de ' . $_ENV['organisation_capital'];
        }
        if (!empty($_ENV['organisation_phone']) && !empty($_ENV['organisation_phone'])) {
            $html .= '<br />Téléphone : ' . $_ENV['organisation_phone'];
        }
        if (!empty($_ENV['organisation_email']) && !empty($_ENV['organisation_email'])) {
            $html .= '<br />Email : ' . $_ENV['organisation_email'];
        }
        if (!empty($_ENV['organisation_url']) && !empty($_ENV['organisation_url'])) {
            $html .= '<br />Web : ' . $_ENV['organisation_url'];
        }
        $html .= '</td>
                            <td stle="width:50%;" valign="top">
                                <div style="border:1px solid #666; border-radius: 6px; -moz-border-radius: 6px; background-color: #ccc; vertical-align: middle; text-align: center; width: 205px; height: 20px; padding-top:4px; margin-left:130px;">' . (($this->type == 'F') ? 'Facture' : 'Devis') . ' en € n° ' . $this->ident . '</div>
                                <div style="margin-top:5px; margin-left:130px; font-size:10px; text-align: right;">Le ' . date('d/m/Y', strtotime($this->date_invoice)) . '</div>
                                <div style="margin-left:130px; margin-top:10px;">
                                    ' . nl2br($this->address) . '
                                </div>
                            </td>
                        </tr>
                        ' . (($this->details) ? '
                        <tr>
                          <td colspan="2">
                            ' . $this->details . '
                          </td>
                        </tr>
                        ' : '') . '
                        <tr>
                            <td colspan="2">
                                <div style="margin-top:20px">
                                    <table cellpading="0" cellspacing="0" style="font-size:11px; width:100%; border:1px solid #666;">
                                        <thead>
                                            <tr>
                                                <th style="width:500px;" width="70%">DESIGNATION</th>
                                                <th style="border-left:1px solid #666" width="15%">TVA</th>
                                                <th style="border-left:1px solid #666" width="15%">MONTANT HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
        $vats = array();
        $vat_total = array(
            'ht' => 0,
            'vat' => 0
        );
        foreach ($this->items as $item) {
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
            if ($item->ressource_id) {

                $html .= '
                                            <tr valign="top">
                                                <td style="border-top:1px solid #666; padding:5px">' . $item->text . '</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">' . $item->vat->value . '%</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">' . sprintf('%0.2f', $item->amount) . '€</td>
                                            </tr>
                                            ';
            } else {
                $html .= '
                                            <tr valign="top">
                                                <td style="border-top:1px solid #666; padding:5px">' . nl2br($item->text) . '</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; ">&nbsp;</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; ">&nbsp;</td>
                                            </tr>
                                            ';
            }
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
                                                                <th style="width: 60%; text-align:left; border-right:1px solid #666;" width="50%">Total HT</th>
                                                                <td style="padding-left:5px; text-align:right; border-bottom:1px dashed #666" width="50%">' . sprintf('%0.2f', $vat_total['ht']) . '€</td>
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
                                ' . (($this->type == 'F') ? (($this->date_payment) ? 'Facture payée le ' . date('d/m/Y', strtotime($this->date_payment)) : 'Cette facture est payable à réception de facture') : 'Ce devis est valide jusqu\'au ' . date('d/m/Y', strtotime($this->deadline))) . '
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="font-size:12px;">' . $this->business_terms . '</div>
                    <table cellpading="0" cellspacing="0" style="width:100%">
                        <tr>
                            <td style="width:45%" valign="top">
                                <table cellpading="0" cellspacing="0" style="wwidth:98%; font-size:11px; border-radius: 6px; -moz-border-radius: 6px; border: 1px solid #666; padding:5px;">
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


        return $html;
    }

    public function __toString()
    {
        return sprintf('<a href="%s">%s</a>',
            URL::route('invoice_modify', $this->id), $this->ident);
    }

    public function send()
    {
        $target_user = null;

        $invoice = $this;
        if ($invoice->user) {
            $target_user = $invoice->user;
        }
        if ($invoice->organisation && $invoice->organisation->accountant) {
            $target_user = $invoice->organisation->accountant;
        }
        if (!$target_user) {
            return Redirect::route('invoice_list')
                ->with('mError', sprintf('Aucun utilisateur trouvé pour envoyer la facture %s par email', $invoice->ident));
        }
        Mail::send('emails.invoice', array('invoice' => $invoice), function ($message) use ($invoice, $target_user) {
            $message->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_bcc']);

            $message->to($target_user->email, $target_user->fullname);

            $message->subject(sprintf('%s - Facture %s', $_ENV['organisation_name'], $invoice->ident));

            $pdf = App::make('snappy.pdf.wrapper');
            try {
                $message->attachData($pdf->getOutputFromHtml($invoice->getPdfHtml()),
                    sprintf('%s.pdf', $invoice->ident), array('mime' => 'application/pdf'));
            } catch (\RuntimeException $e) {
                //
            }
        });

        $to = htmlentities(sprintf('%s <%s>', $target_user->fullname, $target_user->email));

        $invoice_comment = new InvoiceComment();
        $invoice_comment->invoice_id = $invoice->id;
        $invoice_comment->user_id = Auth::id();
        $invoice_comment->content = sprintf('Envoyé par email le %s à %s', date('d/m/Y'), $to);
        $invoice_comment->save();

        $invoice->sent_at = date('Y-m-d');
        $invoice->save();

        return sprintf('La facture %s a été envoyée par email à %s', $invoice->ident, $to);
    }
}
