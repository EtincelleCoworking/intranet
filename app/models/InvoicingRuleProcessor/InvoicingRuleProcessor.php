<?php

class InvoicingRuleProcessor
{

    public function __construct(InvoicingRule $rule)
    {

    }

    public function execute($invoice_lines)
    {
        return $invoice_lines;
    }

    public static function getCaption()
    {
        return '';
    }

    public function isValidForQuotes()
    {
        return true;
    }

    public function isValidForInvoices()
    {
        return true;
    }

    public static function getAvailableItems()
    {
        $result = array();
        $dh = dir(__DIR__);
        while ($filename = $dh->read()) {
            if (preg_match('/(.+)\.php$/', $filename, $tokens)) {
                $className = 'InvoicingRuleProcessor_' . $tokens[1];
                if (class_exists($className)) {
                    $result[$className] = $className::getCaption();
                }
            }
        }
        return $result;
    }

}