<?php

/**
 * InvoicingRule Entity
 */
class InvoicingRule extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invoicing_rules';

    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

    public $timestamps = false;

    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    public function createProcessor()
    {
        $className = $this->kind;
        if (class_exists($className)) {
            return new $className($this);
        }
        return null;
    }

}