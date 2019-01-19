<?php

namespace Accounting;

class Supplier extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'accounting_suppliers';

    /**
     * Rules
     */
    public static $rules = array(
        'name' => 'required',
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'name' => 'required',
    );
}