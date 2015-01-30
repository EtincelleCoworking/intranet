<?php
/**
* Charge Item Entity
*/
class ChargeItem extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'charges_items';

    /**
     * Item belongs to Charge
     */
    public function charge()
    {
        return $this->belongsTo('Charge');
    }

    /**
     * Item belongs to Vat
     */
    public function vat()
    {
        return $this->belongsTo('VatType', 'vat_types_id');
    }
}