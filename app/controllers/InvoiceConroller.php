<?php
/**
* Invoice Controller
*/
class InvoiceController extends BaseController
{

	/**
	 * Default template
	 */
	protected $layout = "layouts.master";

	/**
	 * List invoices
	 */
	public function liste()
	{
		$invoices = Invoice::orderBy('created_at', 'DESC')->paginate(15);

		$this->layout->content = View::make('invoice.liste', array('invoices' => $invoices));
	}

	/**
	 * Modify invoice
	 */
	public function modify($id)
	{
		$invoice = Invoice::find($id);
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

		$this->layout->content = View::make('invoice.modify', array('invoice' => $invoice, 'date_explode' => $date_explode, 'dead_explode' => $dead_explode, 'payment_explode' => $payment_explode));
	}

	/**
	 * Modify invoice (form)
	 */
	public function modify_check($id)
	{
		$invoice = Invoice::find($id);
		if (!$invoice) {
			return Redirect::route('invoice_list')->with('mError', 'Cette facture est introuvable !');
		}

        Input::merge(array('date_invoice' => Input::get('year').'-'.Input::get('month').'-'.Input::get('day')));
		$validator = Validator::make(Input::all(), Invoice::$rules);
		if (!$validator->fails()) {
            $invoice->date_invoice = Input::get('year').'-'.Input::get('month').'-'.Input::get('day');
            $invoice->deadline = Input::get('dead_year').'-'.Input::get('dead_month').'-'.Input::get('dead_day');
            if (Input::get('is_paid')) {
                $invoice->date_payment = Input::get('payment_year').'-'.Input::get('payment_month').'-'.Input::get('payment_day');
            } else {
                $invoice->date_payment = null;
            }

            if ($invoice->save()) {
                return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été modifiée');
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
	public function add()
	{
		$last_organisation_id = Input::old('organisation_id');
		$this->layout->content = View::make('invoice.add', array('last_organisation_id' => $last_organisation_id));
	}

	/**
	 * Add Invoice check
	 */
	public function add_check()
	{
		$validator = Validator::make(Input::all(), Invoice::$rulesAdd);
		if (!$validator->fails()) {
			$days = Input::get('year').Input::get('month');

			$invoice = new Invoice;
			$invoice->user_id = Input::get('user_id');
			$invoice->organisation_id = Input::get('organisation_id');
			$invoice->type = Input::get('type');
			$invoice->days = $days;
            $invoice->date_invoice = Input::get('year').'-'.Input::get('month').'-'.Input::get('day');
			$invoice->number = Invoice::next_invoice_number(Input::get('type'), $days);

            $date = new DateTime($invoice->date_invoice);
            $date->modify('+30 days');
            $invoice->deadline = $date->format('Y-m-d');

			if ($invoice->save()) {
				return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été ajoutée');
			} else {
				return Redirect::route('invoice_add')->with('mError', 'Impossible de créer cette facture')->withInput();
			}
		} else {
			return Redirect::route('invoice_add')->with('mError', 'Il y a des erreurs')->withInput()->withErrors($validator->messages());
		}
	}

	/**
     * Validate a quotation
     */
    public function validate($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return Redirect::route('invoice_list')->with('mError', 'Cette facture est introuvable !');
        }

        $invoice->type = 'F';
        $invoice->number = Invoice::next_invoice_number('F', $invoice->days);

        if ($invoice->save()) {
            return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été générée');
        } else {
            return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Impossible de générer la facture');
        }
    }

    /**
	 * Delete a quotation
	 */
	public function delete($id)
	{
        if (Invoice::destroy($id)) {
            return Redirect::route('invoice_list')->with('mSuccess', 'Le devis a bien été supprimé');
        } else {
            return Redirect::route('invoice_modify', $id)->with('mError', 'Impossible de supprimer ce devis');
        }
	}

    /**
     * Print invoice to PDF
     */
    public function print_pdf($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return Redirect::route('invoice_list')->with('mError', 'Cette facture est introuvable !');
        }

        $snappy = App::make('snappy.pdf');

        $html='
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
                <title>'.$invoice->ident.'</title>
            </head>
            <body>
                <table style="font-size:12px; width:100%">
                    <tbody>
                        <tr>
                            <td style="width:50%">
                                <strong>'.$_ENV['organisation_name'].' '.$_ENV['organisation_status'].'</strong><br />
                                '.$_ENV['organisation_address'].'<br />
                                '.$_ENV['organisation_zipcode'].' '.$_ENV['organisation_city'].'<br />
                                '.$_ENV['organisation_country'].'<br />
                                <br />
                                SIRET : '.$_ENV['organisation_siret'].'<br />
                                TVA Intracommunautaire : '.$_ENV['organisation_tva'].'<br />
                                '.$_ENV['organisation_status'].' au capital de '.$_ENV['organisation_capital'].'
                            </td>
                            <td stle="width:50%;" valign="top">
                                <div style="border:1px solid #666; border-radius: 6px; -moz-border-radius: 6px; background-color: #ccc; vertical-align: middle; text-align: center; width: 205px; height: 20px; padding-top:4px; margin-left:130px;">'.(($invoice->type == 'F') ? 'Facture' : 'Devis').' en € n° '.$invoice->ident.'</div>
                                <div style="margin-top:5px; margin-left:130px; font-size:10px; text-align: right;">Le '.date('d/m/Y', strtotime($invoice->date_invoice)).'</div>
                                <div style="margin-left:130px; margin-top:10px;">
                                    '.$invoice->organisation->name.'<br />
                                    '.$invoice->organisation->address.'<br />
                                    '.$invoice->organisation->zipcode.' '.$invoice->organisation->city.'<br />
                                    '.$invoice->organisation->country->name.'
                                </div>
                            </td>
                        </tr>
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
                                                <td style="border-top:1px solid #666; padding:5px">'.nl2br($item->text).'</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">'.$item->vat->value.'%</td>
                                                <td style="border-top:1px solid #666; border-left:1px solid #666; text-align:right; padding:5px">'.sprintf('%0.2f', $item->amount).'€</td>
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
                                                                    <td style="text-align:right">'.sprintf('%0.2f', $vat['taux']).'%</td>
                                                                    <td style="text-align:right">'.sprintf('%0.2f', $vat['montant']).'€</td>
                                                                    <td style="text-align:right">'.sprintf('%0.2f', $vat['base']).'€</td>
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
                                                                <td style="padding-left:5px; text-align:right; border-bottom:1px dashed #666">'.sprintf('%0.2f', $vat_total['ht']).'€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left; border-right:1px solid #666;">Montant TVA</th>
                                                                <td style="padding-left:5px; text-align:right; border-bottom:1px dashed #666">'.sprintf('%0.2f', $vat_total['vat']).'€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left; border-right:1px solid #666;">Total TTC</th>
                                                                <td style="padding-left:5px; text-align:right;">'.sprintf('%0.2f', ($vat_total['ht'] + $vat_total['vat'])).'€</td>
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
                                '.(($invoice->type == 'F') ? (($invoice->date_payment) ? 'Facture payée le '.date('d/m/Y', strtotime($invoice->date_payment)) : 'Cette facture est payable avant le '.date('d/m/Y', strtotime($invoice->deadline))) : 'Ce devis est valide jusqu\'au '.date('d/m/Y', strtotime($invoice->deadline))).'
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
                                            <td>10057</td>
                                            <td>19053</td>
                                            <td>00020074201</td>
                                            <td>51</td>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <th colspan="3" style="text-align:left">IBAN</th>
                                            <th style="text-align:left">BIC</th>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <td colspan="3">FR76 1005 7190 5300 0200 7420 151</td>
                                            <td>CMCIFRPP</td>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <th colspan="4">Domiciliation</th>
                                        </tr>
                                        <tr style="font-size:10px;">
                                            <td colspan="4">CIC MONTAUBAN</td>
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
        return $pdf->stream($invoice->ident.'.pdf');
    }
}