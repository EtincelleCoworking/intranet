<?php

class BankOperationFactory extends AbstractBankOperationFactory
{
    protected $operation;

    public function __construct(BankOperation $operation)
    {
        $this->operation = $operation;
    }

    public function populate(BankOperationCollection $collection)
    {
        if ($this->operation->getOccursAt() < $collection->getEndsAt()) {
            $collection->register($this->operation);
        }
    }
}
