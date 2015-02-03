<?php
/**
* ChargePayment entity
*/
class ChargePayment extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'charges_payments';

    /**
     * Payment belongs to Charge
     */
    public function charge()
    {
        return $this->belongsTo('Charge');
    }
}