<?php
/**
* Charge Item Controller
*/
class ChargeItemController extends BaseController
{
    /**
     * Default template
     */
    protected $layout = "layouts.master";


    /**
     * Add item to charge
     */
    private function add_check($d)
    {
        $item = new ChargeItem;
        return $item->insert($d);
    }

    /**
     * Modify item to charge
     */
    public function modify($id)
    {
        $charge = Charge::find($id);

        foreach ($charge->items as $item) {
            ChargeItem::where('id', $item->id)->update(array(
                'description' => Input::get('description.'.$item->id),
                'amount' => Input::get('amount.'.$item->id),
                'vat_types_id' => Input::get('vat_types_id.'.$item->id),
            ));
        }

        // Add new line
        if (Input::get('description.0')) {
            $this->add_check(array(
                'charge_id' => $id,
                'description' => Input::get('description.0'),
                'amount' => Input::get('amount.0'),
                'vat_types_id' => Input::get('vat_types_id.0')
            ));
        }

        return Redirect::route('charge_modify', $id);
    }

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