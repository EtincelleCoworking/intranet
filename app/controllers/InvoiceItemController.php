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

    protected function getInputData($fieldIndex, $fields)
    {
        $result = array();
        foreach ($fields as $fieldName => $fieldKind) {
            if ($fieldKind == 'date') {
                $values = explode('/', Input::get($fieldName . '.' . $fieldIndex));
                if (count($values) == 3) {
                    $result[$fieldName] = $values[2] . '-' . $values[1] . '-' . $values[0];
                } else {
                    $result[$fieldName] = null;
                }
            } else {
                $result[$fieldName] = Input::get($fieldName . '.' . $fieldIndex);
            }
        }
        return $result;
    }

    /**
     * Modify item to invoice
     */
    public function modify($id)
    {
        $invoice = Invoice::find($id);

        $fields = array();
        $fields['ressource_id'] = null;
        $fields['text'] = null;
        $fields['vat_types_id'] = null;
        $fields['order_index'] = null;

        $fields['booking_hours'] = null;

        $fields['subscription_user_id'] = null;
        $fields['subscription_hours_quota'] = null;
        $fields['subscription_from'] = 'date';
        $fields['subscription_to'] = 'date';

        foreach ($invoice->items as $item) {
            InvoiceItem::where('id', $item->id)->update($this->getInputData($item->id, $fields));
        }

        // Add new line
        if (Input::get('text.0')) {
            $this->add_check($this->getInputData(0, $fields));
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
