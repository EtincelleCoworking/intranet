<?php

/**
 * Location Entity
 */
class LocationIp extends Eloquent
{
    protected $table = 'locations_ips';
    /**
     * Rules
     */
    public static $rules = array(
        'name' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'name' => 'required|min:1|unique:LocationIp'
    );

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function location()
    {
        return $this->belongsTo('Location', 'id');
    }

    public function getAge(){
        $days = time() - strtotime($this->updated_at);
        $days /= 24 * 60 * 60;
        return $days;
    }
}