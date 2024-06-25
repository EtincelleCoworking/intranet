<?php

class InvoicingRuleProcessor
{

    public function __construct(InvoicingRule $rule)
    {

    }

    public function execute($invoice_lines, $invoice_lines_details)
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
        $result = array('-');
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

    protected function createDiscountLine($invoice_line, $text = '', $amount = 0)
    {
        $new_line = $this->createAdditionnalLine($invoice_line);
        $new_line->ressource_id = $invoice_line->ressource_id;
        $new_line->amount = $amount;
        $new_line->text = $text;
        return $new_line;

    }

    protected function createAdditionnalLine($invoice_line)
    {
        $new_line = new InvoiceItem();
        $new_line->invoice_id = $invoice_line->invoice_id;
        $new_line->vat_types_id = $invoice_line->vat_types_id;
        return $new_line;

    }

}