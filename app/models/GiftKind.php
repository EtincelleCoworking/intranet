<?php

/**
 * Tag Entity
 */
class GiftKind extends Eloquent
{
    const PHOTOSHOOT = 'photoshoot';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gift_kind';


    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();
}