<?php

class InvoiceBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        $invoices = Invoice::invoiceOnly()->unpaid()
            ->where('on_hold', false)
            ->where('is_lost', false)
            ->get();
        foreach ($invoices as $invoice) {
            /** @var Invoice $invoice */
            $occurs_at = $invoice->expected_payment_at;
            $operation = new ManagedBankOperation($occurs_at, $invoice->caption, Invoice::TotalInvoiceWithTaxes($invoice->items));
            $operation->setComment(sprintf('%s€ HT', Invoice::TotalInvoice($invoice->items)));

            $operation->setEditLink(URL::route('invoice_modify', $invoice->id));
            $operation->registerAction(new BankOperationAction\Reminder(URL::route('organisation_remind', $invoice->id)));
            $operation->registerAction(new BankOperationAction\Pdf(URL::route('invoice_print_pdf', $invoice->id)));
            $operation->registerAction(new BankOperationAction\Archive(URL::route('invoice_paid', $invoice->id)));

            $collection->register($operation);
        }
    }
}