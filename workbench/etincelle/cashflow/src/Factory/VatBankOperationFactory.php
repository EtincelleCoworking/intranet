<?php

class VatBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        $start_at = date('Y-m-d');

        // Si on est en début de trimestre, inclure la TVA collectée au trimestre précédent
        if (date('d') <= 15 && in_array(date('m'), array(1, 4, 7, 10))) {
            $start_at = (new \DateTime($start_at))->modify('-3 months')->format('Y-m-d');
        }

        while ($start_at < $collection->getEndsAt()) {
            $start_at_ts = strtotime($start_at);
            $begin_at = date('Y-m-d', mktime(0, 0, 0,
                floor(date('m', $start_at_ts) / 3) * 3 + 1,
                1, date('Y', $start_at_ts)
            ));
            $end_at = date('Y-m-t', mktime(0, 0, 0,
                ceil(date('m', $start_at_ts) / 3) * 3,
                1, date('Y', $start_at_ts)
            ));
            $occurs_at = (new \DateTime($end_at))->modify('+15 days')->format('Y-m-d');
            $invoices = Invoice::invoiceOnly()
                ->where('date_payment', '>=', $begin_at)
                ->where('date_payment', '<=', $end_at)
                ->get();
            $vat = 0;
            foreach ($invoices as $invoice) {
                $vat += Invoice::TotalInvoiceWithTaxes($invoice->items) - Invoice::TotalInvoice($invoice->items);
            }
            $collection->register(new BankOperation($occurs_at, sprintf('TVA collectée entre le %s et le %s', date('d/m/Y', strtotime($begin_at)), date('d/m/Y', strtotime($end_at))), -$vat));
            $start_at = (new \DateTime($start_at))->modify('+3 months')->format('Y-m-d');
        }
    }
}