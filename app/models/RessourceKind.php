<?php

class RessourceKind extends Eloquent
{
    protected $table = 'ressource_kind';

    const TYPE_COWORKING = 1;
    const TYPE_EXCEPTIONNAL = 4;
    const TYPE_MEETING_ROOM = 2;

    public function __toString(){
        return $this->name;
    }

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query)
    {
        return $query->orderBy('order_index', 'ASC')->lists('name', 'id');
    }
}