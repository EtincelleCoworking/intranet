<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class InvoiceContext extends BaseContext {

    public function __construct() { }

    /**
     * @Given /^I am "([^"]*)"$/
     */
    public function iAm($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I go to the "([^"]*)"$/
     */
    public function iGoToThe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I must see all invoices$/
     */
    public function iMustSeeAllInvoices()
    {
        throw new PendingException();
    }
}
