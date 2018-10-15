<?php

/**
 * City Entity
 */
class Job extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'jobs';


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
        'name' => 'required|min:1'
    );



    /**
     * Get list of ressources
     */
    public static function getSlugs()
    {
        $result = array();
        foreach(DB::select('SELECT LOWER(name) as name, id FROM jobs') as $item){
            $result[$item->name] = $item->id;
        }
        return $result;
    }

}