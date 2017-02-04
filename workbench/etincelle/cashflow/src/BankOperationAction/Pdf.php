<?php

namespace BankOperationAction;

class Pdf extends \BankOperationAction
{
    public function __construct($url)
    {
        parent::__construct($url, 'btn-default', 'fa-download');
        $this->setTarget('_blank');
    }

}