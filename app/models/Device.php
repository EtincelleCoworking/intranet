<?php

/**
 * City Entity
 */
class Device extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'devices';


    /**
     * Rules
     */
    public static $rules = array(
        'mac' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'mac' => 'required|min:1'
    );

    protected $fillable = array('mac', 'user_id', 'name');


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

}