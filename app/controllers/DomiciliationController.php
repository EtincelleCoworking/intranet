<?php

class DomiciliationController extends BaseController
{
    public function liste()
    {
        $companies = Organisation::Domiciliation()->paginate(15);

        return View::make('domiciliation.liste', array('companies' => $companies));
    }

    public function renew($id)
    {
        $organisation = Organisation::find($id);
        if (!$organisation) {
            App::abort(404);
        }

        $invoice = new Invoice();
        $invoice->user_id = $organisation->accountant_id;
        $invoice->created_at = new \DateTime();
        $invoice->organisation_id = $organisation->id;
        $invoice->type = 'F';
        $invoice->days = date('Ym');
        $invoice->number = $invoice->next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $organisation->fulladdress;
        $invoice->date_invoice = new \DateTime();
        $invoice->deadline = new \DateTime(date('Y-m-d', strtotime('+1 month')));
        $invoice->save();
        $vat = VatType::where('value', 20)->first();

        $orderIndex = 0;
        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->amount = 0;
        $invoice_line->order_index = $orderIndex++;

        $invoice_line->text = sprintf("Domiciliation commerciale\nDu %s au %s", date('d/m/Y'), date(sprintf('t/%02d/Y', ((date('m') % 3) + 1) * 3)));
        $invoice_line->amount = 35 * (date('d') / date('t') + ((date('m') % 3) + 1) * 3 - date('m'));

        $invoice_line->vat_types_id = $vat->id;
        $invoice_line->ressource_id = Ressource::TYPE_DOMICILIATION;
        $invoice_line->save();

        return Redirect::route('invoice_modify', array('id' => $invoice->id))
            ->with('mSuccess', 'La facture a été générée');

    }

}
