<?php

class SubscriptionKind extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscription_kind';


    public function ressource()
    {
        return $this->belongsTo('Ressource', 'ressource_id');
    }

    public function scopeSelectAll($query)
    {
        $selectVals[null] = 'Aucun';
        $selectVals += $query->orderBy('order_index', 'ASC')->lists('name', 'id');
        return $selectVals;
    }

    public function scopeSelectOptions($query)
    {
        $selectVals[null] = 'Aucun';
        $selectVals += $query->orderBy('order_index', 'DESC')->lists('name', 'hours_quota');
        return $selectVals;
    }


    public function getShortNameAttribute(){
        return str_replace(array('Coworking - ', ' - %UserName%', 'Abonnement '), array('', '', ''), $this->name);
    }

}