<?php

namespace BankOperationAction;

class Refresh extends \BankOperationAction
{
    public function __construct($url)
    {
        parent::__construct($url, 'btn-default', 'fa-refresh');
    }

}