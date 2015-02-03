<?php
/**
* ChargePayment Controller
*/
class ChargePaymentController extends BaseController
{
    public function delete($charge, $id)
    {
        if (ChargePayment::destroy($id)) {
            return Redirect::route('charge_modify', $charge)->with('mSucces', 'Le paiement a bien été supprimé');
        } else {
            return Redirect::route('charge_modify', $charge)->with('mError', 'Impossible de supprimer ce paiement');
        }
    }
}