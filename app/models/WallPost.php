<?php

class WallPost extends \Gzero\EloquentTree\Model\Tree{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wall_posts';

    /**
     * Relation BelongsTo (Vat_Types belongs to Invoices_Items)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'message' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'message' => 'required|min:1'
    );

//    /**
//     * Get list of vat
//     */
//    public function scopeSelectAll($query)
//    {
//        $selectVals = $this->orderBy('value', 'DESC')->lists('value', 'id');
//        return $selectVals;
//    }
}