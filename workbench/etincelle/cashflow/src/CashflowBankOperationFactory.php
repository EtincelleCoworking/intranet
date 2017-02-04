<?php

class CashflowBankOperationFactory extends AbstractBankOperationFactory
{
    protected $account_id;

    public function __construct($account_id)
    {
        $this->account_id = $account_id;
    }

    public function populate(BankOperationCollection $collection)
    {
        $operations = CashflowOperation::where('account_id', $this->account_id)
            ->where('archived', false)
            ->where('occurs_at', '<', $collection->getEndsAt())
            ->get();
        foreach ($operations as $cashflow_operation) {
            /** @var CashflowOperation $cashflow_operation */
            $bank_operation = $cashflow_operation->buildBankOperation();
            if ($bank_operation instanceof RecurringBankOperation) {
                (new RecurringBankOperationFactory($bank_operation))->populate($collection);
            } else {
                (new BankOperationFactory($bank_operation))->populate($collection);
            }
        }
    }
}