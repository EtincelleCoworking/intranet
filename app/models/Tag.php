<?php
/**
* Tag Entity
*/
class Tag extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * Tag belongs to many charges
     */
    public function charges()
    {
        return $this->belongsToMany('Charge', 'charge_tag', 'tag_id', 'charge_id');
    }

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
        'name' => 'required|min:1|unique:tags'
    );

}