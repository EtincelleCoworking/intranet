<?php

/**
 * City Entity
 */
class Metric extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'metrics';


    /**
     * Rules
     */
    public static $rules = array(
        'slug' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'slug' => 'required|min:1'
    );

}