<?php

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrganisationUsageExport implements FromView
{
    public function view(): View
    {
        return view('exports.invoices', [
            'invoices' => Invoice::all()
        ]);
    }
}
