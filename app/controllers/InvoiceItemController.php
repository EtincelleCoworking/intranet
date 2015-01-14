<?php
/**
* InvoiceItem Controller
*/
class InvoiceItemController extends BaseController
{

    /**
     * Add item to invoice
     */
    private function add_check($d)
    {
        $item = new InvoiceItem;
        return $item->insert($d);
    }

	/**
	 * Modify item to invoice
	 */
	public function modify($id)
	{
		$invoice = Invoice::find($id);
        /**
         * Partie à revoir (optimisation)
         */
        foreach ($invoice->items as $item) {
            InvoiceItem::where('id', $item->id)->update(array(
                'text' => Input::get('text.'.$item->id),
                'amount' => Input::get('amount.'.$item->id),
                'vat_types_id' => Input::get('vat_types_id.'.$item->id),
            ));
            /*
            $d = array(
                new InvoiceItem(array(
                        'id' => $item->id,
                        'text' => Input::get('text.'.$item->id),
                        'amount' => Input::get('amount.'.$item->id),
                        'vat_types_id' => Input::get('vat_types_id.'.$item->id),
                    ))
            );
            */
        }
        //$invoice->items()->saveMany($d);

        // Add new line
        if (Input::get('text.0')) {
            $this->add_check(array(
                'invoice_id' => $id,
                'text' => Input::get('text.0'),
                'amount' => Input::get('amount.0'),
                'vat_types_id' => Input::get('vat_types_id.0')
            ));
        }

        return Redirect::route('invoice_modify', $id);
	}

	/**
	 * Delete item to invoice
	 */
	public function delete($invoice, $id)
	{
		if (InvoiceItem::destroy($id)) {
            return Redirect::route('invoice_modify', $invoice)->with('mSucces', 'La ligne a bien été supprimée');
        } else {
            return Redirect::route('invoice_modify', $invoice)->with('mError', 'Impossible de supprimer cette ligne');
        }
	}
}