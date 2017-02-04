<?php

abstract class AbstractBankOperationFactory
{
    public abstract function populate(BankOperationCollection $collection);
}