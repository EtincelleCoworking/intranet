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
		$invoices = Invoice::paginate(15);

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

		$this->layout->content = View::make('invoice.modify', array('invoice' => $invoice));
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

		$validator = Validator::make(Input::all(), Invoice::$rules);
		if (!$validator->fails()) {
			
		} else {
			return Redirect::route('invoice_modify', $invoice->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}

	/**
	 * Add invoice
	 */
	public function add()
	{
		$this->layout->content = View::make('invoice.add');
	}

	/**
	 * Add Invoice check
	 */
	public function add_check()
	{
		$validator = Validator::make(Input::all(), Invoice::$rulesAdd);
		if (!$validator->fails()) {
			$invoice = new Invoice;
			$invoice->user_id = Input::get('user_id');
			$invoice->organisation_id = Input::get('organisation_id');

			if ($invoice->save()) {
				return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'La facture a bien été ajoutée');
			} else {
				return Redirect::route('invoice_add')->with('mError', 'Impossible de créer cette facture')->withInput();
			}
		} else {
			return Redirect::route('invoice_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}
}