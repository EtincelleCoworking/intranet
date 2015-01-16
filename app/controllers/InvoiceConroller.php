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

		$this->layout->content = View::make('invoice.modify', array('invoice' => $invoice, 'date_explode' => $date_explode, 'dead_explode' => $dead_explode));
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
     * Print invoice to PDF
     */
    public function print_pdf($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return Redirect::route('invoice_list')->with('mError', 'Cette facture est introuvable !');
        }

        $html='
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
            </head>
            <body>
                <table style="font-size:14px; width:100%">
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
                                <div style="border-radius: 6px; -moz-border-radius: 6px; background-color: #757978; vertical-align: middle; text-align: center; width: 205px; height: 20px; padding-top:4px; margin-left:130px;">'.(($invoice->type == 'F') ? 'Facture' : 'Devis').' en € n° '.$invoice->ident.'</div>
                                <div style="margin-top:5px; margin-left:130px;">Le '.date('d/m/Y', strtotime($invoice->date_invoice)).'</div>
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
                                    <table cellpading="0" cellspacing="0" style="width:100%;" border="1">
                                        <thead>
                                            <tr>
                                                <th style="width:500px">DESIGNATION</th>
                                                <th>MONTANT HT</th>
                                                <th>TVA</th>
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
                                                <td>'.nl2br($item->text).'</td>
                                                <td>'.sprintf('%0.2f', $item->amount).'€</td>
                                                <td>'.$item->vat->id.'</td>
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
                                <div style="margin-top:10px;">
                                    <div>&nbsp;</div>
                                    <table style="width:100%">
                                        <tbody>
                                            <tr>
                                                <td width="75%" valign="top">
                                                    <table cellpading="0" cellspacing="0" style="width:98%;" border="1">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="2">BASES HT</th>
                                                                <th>MT TVA</th>
                                                                <th>% TVA</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                                                        foreach ($vats as $k => $vat) {
                                                            $html .= '
                                                                <tr>
                                                                    <td>'.$k.'</td>
                                                                    <td>'.sprintf('%0.2f', $vat['base']).'€</td>
                                                                    <td>'.sprintf('%0.2f', $vat['montant']).'€</td>
                                                                    <td>'.sprintf('%0.2f', $vat['taux']).'%</td>
                                                                </tr>
                                                            ';
                                                        }
        $html .= '
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td valign="top" style="text-align:left;">
                                                    <table cellpading="0" cellspacing="0" style="width:100%;;" border="1">
                                                        <tbody>
                                                            <tr>
                                                                <th style="text-align:left;">Total HT</th>
                                                                <td>'.sprintf('%0.2f', $vat_total['ht']).'€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left;">Montant TVA</th>
                                                                <td>'.sprintf('%0.2f', $vat_total['vat']).'€</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="text-align:left;">Total TTC</th>
                                                                <td>'.sprintf('%0.2f', ($vat_total['ht'] + $vat_total['vat'])).'€</td>
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
                                '.(($invoice->type == 'F') ? 'Cette facture est payable avant le ' : 'Ce devis est valide jusqu\'au ').'
                                '.date('d/m/Y', strtotime($invoice->deadline)).'
                            </td>
                        </tr>
                    </tbody>
                </table>
            </body>
        </html>';
        //return PDF::load($html, 'A4', 'portrait')->download($invoice->ident);
        return PDF::load($html, 'A4', 'portrait')->show();
    }
}