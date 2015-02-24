<?php
/**
* InvoiceComment Controller
*/
class InvoiceCommentController extends BaseController
{
	public function add($invoice)
	{
		$validator = Validator::make(Input::all(), InvoiceComment::$rulesAdd);
		if (!$validator->fails()) {
			$i = new InvoiceComment;
			$i->invoice_id = Input::get('invoice_id');
			$i->user_id = Input::get('user_id');
			$i->content = Input::get('content');
			if ($i->save()) {
				return Redirect::route('invoice_modify', $invoice)->with('mSuccess', 'Le commentaire a bien été ajouté');
			}
		}

		return Redirect::route('invoice_modify', $invoice)->with('mError', 'Impossible d\'ajouter ce commentaire');
	}
}