<?php

/**
 * Location Entity
 */
class Location extends Eloquent
{
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
        'name' => 'required|min:1|unique:Location'
    );

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query, $includeEmpty = true)
    {
        $selectVals = array();
        if ($includeEmpty) {
            $selectVals[null] = '-';
        }
        $selectVals += $query
            ->join('cities', 'cities.id', '=', 'locations.city_id')
            ->select(array('locations.id', DB::raw('concat(cities.name, \' > \', locations.name) as _name')))
            ->where('enabled', '=', true)
            ->orderBy('cities.name', 'ASC')
            ->orderBy('locations.name', 'ASC')
            ->lists('_name', 'id');
        foreach ($selectVals as $k => $v) {
            $selectVals[$k] = rtrim($v, ' > ');
        }
        return $selectVals;
    }

    public function __toString()
    {
        return (string)$this->full_name;
    }

    public function getFullNameAttribute()
    {
        if ($this->name) {
            return sprintf('%s > %s', $this->city->name, $this->name);
        }
        return $this->city->name;
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function city()
    {
        return $this->belongsTo('City', 'city_id');
    }


}