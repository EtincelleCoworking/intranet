<?php

class Sensor extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sensors';

    /**
     * Rules
     */
    public static $rules = array(
        'location_id' => 'required|exists:locations',
        'name' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'location_id' => 'required|exists:locations',
        'name' => 'required|min:1'
    );


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

}