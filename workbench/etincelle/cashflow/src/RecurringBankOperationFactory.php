<?php

class RecurringBankOperationFactory extends AbstractBankOperationFactory
{
    protected $operation;

    public function __construct(RecurringBankOperation $operation)
    {
        $this->operation = $operation;
    }

    public function populate(BankOperationCollection $collection)
    {
        $start_at = $this->operation->getOccursAt();
        while ($start_at < $collection->getEndsAt()) {
            $collection->register($this->operation->buildOccurence($start_at));
            $start_at = (new \DateTime($start_at))
                ->modify(sprintf('+%s', $this->operation->getFrequency()))
                ->format('Y-m-d');
        }
    }
}