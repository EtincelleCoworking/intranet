<?php

namespace Accounting;

class BankOperationImport extends \Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'accounting_bank_operations_import';

    /**
     * Rules
     */
    public static $rules = array(
        'filename' => 'required',
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'filename' => 'required',
    );
}