<?php

namespace BankOperationAction;

class Reminder extends \BankOperationAction
{
    public function __construct($url)
    {
        parent::__construct($url, 'btn-default', 'fa-bell');
    }

}