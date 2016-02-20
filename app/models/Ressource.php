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
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function kind()
    {
        return $this->belongsTo('RessourceKind', 'ressource_kind_id');
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

    public function getLabelCssAttribute()
    {
        return sprintf('background-color: %s; color: %s; border: 1px solid %s; margin-right: 10px; opacity: 0.75',
            $this->booking_background_color,
            adjustBrightness($this->booking_background_color, -128),
            adjustBrightness($this->booking_background_color, -32)
        );
    }

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query)
    {
        $selectVals[null] = 'Aucune ressource';
        $selectVals += $query->orderBy('order_index', 'ASC')->lists('name', 'id');
        return $selectVals;
    }

    /**
     * Get list of ressources
     */
    public function scopeBookable($query, $emptyCaption = '')
    {
        $selectVals = array();
        if (!empty($emptyCaption)) {
            $selectVals[null] = $emptyCaption;
        }
        $selectVals += $query->whereIsBookable(true)->orderBy('order_index', 'ASC')->lists('name', 'id');
        return $selectVals;
    }
}