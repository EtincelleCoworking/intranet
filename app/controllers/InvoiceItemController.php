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
                    $result[$fieldName] = '0000-00-00 00:00:00';
                }
            } elseif ($fieldKind == 'integer') {
                $result[$fieldName] = (int)Input::get($fieldName . '.' . $fieldIndex);
            } elseif ($fieldKind == 'object') {
                $object_id = (int)Input::get($fieldName . '.' . $fieldIndex);
                $result[$fieldName] = $object_id ? $object_id : null;
            } elseif ($fieldKind == 'price') {
                $result[$fieldName] = str_replace(',', '.', Input::get($fieldName . '.' . $fieldIndex));
            } else {
                $value = Input::get($fieldName . '.' . $fieldIndex, null);
                $result[$fieldName] = empty($value) ? null : $value;
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
        $fields['amount'] = 'price';
        $fields['vat_types_id'] = null;
        $fields['order_index'] = 'integer';

        $fields['booking_hours'] = 'integer';

        $fields['subscription_user_id'] = 'object';
        $fields['subscription_hours_quota'] = 'integer';
        $fields['subscription_from'] = 'date';
        $fields['subscription_to'] = 'date';
        $fields['coworking_pack_item_count'] = 'integer';
        $fields['coworking_pack_item_user_id'] = 'object';
        $coworking_pack_item_count = array();
        foreach ($invoice->items as $item) {
            $data = $this->getInputData($item->id, $fields);
            InvoiceItem::where('id', $item->id)->update($data);
            if (!empty($data['coworking_pack_item_count'])) {
                $coworking_pack_item_count[$item->id] = $data['coworking_pack_item_count'];
            }
        }

        // Add new line
        if (Input::get('text.0')) {
            $data = $this->getInputData(0, $fields);
            $item = $this->add_check(array_merge(array('invoice_id' => $id), $data));
            if (!empty($data['coworking_pack_item_count'])) {
                $coworking_pack_item_count[$item->id] = $data['coworking_pack_item_count'];
            }
        }

        if (count($coworking_pack_item_count) > 0) {
            foreach ($coworking_pack_item_count as $invoice_item_id => $count) {

                $current_count = CoworkingPrepaidPackItem::where('invoice_item_id', '=', $invoice_item_id)->count();
                if ($current_count > $count) {
                    // trigger error
                } else {
                    for ($i = $current_count; $i < $count; $i++) {
                        $pack_item = new CoworkingPrepaidPackItem();
                        $pack_item->invoice_item_id = $invoice_item_id;
                        $pack_item->index = $i + 1;
                        $pack_item->save();
                    }
                }
            }
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
