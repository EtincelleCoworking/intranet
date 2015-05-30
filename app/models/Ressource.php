<?php
/**
* Ressource Entity
*/
class Ressource extends Eloquent
{
    const TYPE_COWORKING = 1;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ressources';

    /**
     * Relation hasMany (Ressource has many Invoices_Items)
     */
    public function items()
    {
        return $this->hasMany('InvoiceItem');
    }

    /**
     * Relation One To Many (Ressource has many Past Times)
     */
    public function pasttimes()
    {
        return $this->hasMany('PastTime');
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
        'name' => 'required|min:1'
    );

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query)
    {
        $selectVals['null'] = 'Aucune ressource';
        $selectVals += $this->orderBy('order_index', 'ASC')->lists('name', 'id');
        return $selectVals;
    }
}