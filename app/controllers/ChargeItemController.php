<?php
/**
* Charge Item Controller
*/
class ChargeItemController extends BaseController
{
    /**
     * Delete item to charge
     */
    public function delete($charge, $id)
    {
        if (ChargeItem::destroy($id)) {
            return Redirect::route('charge_modify', $charge)->with('mSucces', 'La ligne a bien été supprimée');
        } else {
            return Redirect::route('charge_modify', $charge)->with('mError', 'Impossible de supprimer cette ligne');
        }
    }
}