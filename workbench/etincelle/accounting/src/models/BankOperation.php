<?php

namespace Accounting;

class BankOperation extends \Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'accounting_bank_operations';

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