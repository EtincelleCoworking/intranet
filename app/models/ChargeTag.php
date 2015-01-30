<?php
/**
* Charge Tag Entity
*/
class ChargeTag extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'charge_tag';

    /**
     * ChargeTag belongs to charge
     */
    public function charge()
    {
        return $this->belongsTo('Charge');
    }

    /**
     * ChargeTag belongs to tag
     */
    public function tag()
    {
        return $this->belongsTo('Tag');
    }
}