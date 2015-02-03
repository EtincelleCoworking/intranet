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
     * Charge has many payments
     */
    public function payments()
    {
        return $this->hasMany('ChargePayment');
    }

    /**
     * Charge belongs to Organisation
     */
    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    /**
     * Days before deadline
     */
    public function getDaysDeadlineAttribute()
    {
        $date1 = new DateTime($this->deadline);
        $date2 = new DateTime();
        $diff = $date2->diff($date1);

        if ($this->deadline >= date('Y-m-d')) {
            return $diff->days;
        } else {
            if ($this->deadline) { $prefix = '-'; }
            else { $prefix = ''; }

            return $prefix.$diff->days;
        }
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
     * Total TVA
     */
    public function getTotalVatAttribute()
    {
        $total = 0;

        if ($this->items) {
            foreach ($this->items as $key => $value) {
                $total += round((($value->amount * $value->vat->value) / 100), 2);
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
        'deadline' => 'required',
        'document'    =>  'mimes:pdf,gif,jpg,png'
    );
}