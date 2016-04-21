<?php

class DomiciliationKind extends Eloquent
{
    protected $table = 'domiciliation_kind';

    public function __toString()
    {
        return $this->name;
    }

    public function scopeSelect($query)
    {
        $selectVals[''] = '';
        $query = $this;
        $query = $query->orderBy('name', 'asc');
        $selectVals += $query->get()->lists('name', 'id');
        return $selectVals;
    }
}