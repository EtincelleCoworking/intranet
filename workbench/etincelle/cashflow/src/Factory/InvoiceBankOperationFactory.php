<?php

class InvoiceBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        $invoices = Invoice::invoiceOnly()->unpaid()->get();
        $today = date('Y-m-d');
        foreach ($invoices as $invoice) {
            /** @var Invoice $invoice */
            $start_at = $invoice->date_invoice;
            if ($start_at > $today) {
                $occurs_at = (new \DateTime($start_at))->modify('+1 month')->format('Y-m-d');
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
}