<?php
/**
* Charge Entity
*/
class Charge extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'charges';

    /**
     * Charge has many items
     */
    public function items()
    {
        return $this->hasMany('ChargeItem');
    }

    /**
     * Charge belongs to many Tags
     */
    public function tags()
    {
        return $this->belongsToMany('Tag', 'charge_tag', 'charge_id', 'tag_id');
    }

    /**
     * Total
     */
    public function getTotalAttribute()
    {
        $total = 0;

        if ($this->items) {
            foreach ($this->items as $key => $value) {
                $total += $value->amount;
            }
        }

        return sprintf('%0.2f', $total);
    }

    /**
     * Rules
     */
    public static $rules = array(
        'date_charge' => 'required',
        'document'    =>  'mimes:pdf,gif,jpg,png'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'date_charge' => 'required',
        'document'    =>  'mimes:pdf,gif,jpg,png'
    );
}