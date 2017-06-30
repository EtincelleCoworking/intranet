<?php

class RecurringBankOperation extends BankOperation
{
    protected $frequency;

    public function getFrequency()
    {
        return $this->frequency;
    }

    public function setFrequency($value)
    {
        $this->frequency = $value;
    }

    public function __construct($occurs_at, $name, $amount, $frequency)
    {
        parent::__construct($occurs_at, $name, $amount);
        $this->setFrequency($frequency);
    }

    public function buildOccurence($occurs_at){
        $result = new BankOperation($occurs_at, $this->getName(), $this->getAmount());
        $result->setId($this->id);
        return $result;
    }
}