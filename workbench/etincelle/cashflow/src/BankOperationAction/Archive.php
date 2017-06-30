<?php

namespace BankOperationAction;

class Archive extends \BankOperationAction
{
    public function __construct($url)
    {
        parent::__construct($url, 'btn-primary', 'fa-check text-primary');
    }

}