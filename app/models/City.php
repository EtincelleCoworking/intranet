<?php

/**
 * City Entity
 */
class City extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cities';


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
    public function scopeSelectAll($query)
    {
        $result = array(0 => '-');
        foreach($query->lists('name', 'id') as $id => $name){
            $result[$id] = $name;
        }
        return $result;
    }


    /**
     * Get list of ressources
     */
    public function getSlugs()
    {
        $result = array();
        foreach(DB::select('SELECT LOWER(name) as name, id FROM cities') as $item){
            $result[$item->name] = $item->id;
        }
        return $result;
    }

}