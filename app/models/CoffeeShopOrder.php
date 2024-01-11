<?php

class CoffeeShopOrder extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coffeeshop_orders';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function invoice()
    {
        return $this->belongsTo('Invoice');
    }
}