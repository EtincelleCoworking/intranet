<?php
/**
* Ressource Entity
*/
class Ressource extends Eloquent
{
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
     * Get list of vat
     */
    public function scopeSelectAll($query)
    {
        $selectVals['null'] = 'Aucune ressource';
        $selectVals += $this->lists('name', 'id');
        return $selectVals;
    }
}