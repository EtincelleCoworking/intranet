<?php

/**
 * Tag Entity
 */
class Hashtag extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hashtags';

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
        'name' => 'required|min:1|unique:hashtags'
    );

    public function scopeSelect($query)
    {
        return $query
            ->orderBy('hashtags.name', 'ASC')
            ->lists('hashtags.name', 'hashtags.id');
    }
}