@if(Auth::user()->isSuperAdmin())
    <?php
    $chargesMonth = DB::table('charges_items')->join('charges', function ($join) {
        $join->on('charges_items.charge_id', '=', 'charges.id')
                ->where(DB::raw('YEAR(charges.date_charge)'), '=', date('Y'))
                ->where(DB::raw('YEAR(charges.deadline)'), '=', date('Y'))
                ->where(DB::raw('MONTH(charges.date_charge)'), '=', date('n'))
                ->where(DB::raw('MONTH(charges.deadline)'), '=', date('n'))
        ;
    })->join('vat_types', function ($join) {
        $join->on('charges_items.vat_types_id', '=', 'vat_types.id');
    })->select(DB::raw('SUM(amount) as total, SUM(((amount * vat_types.value) / 100)) as mtva'))->first();

    $chargesMonthToPay = DB::table('charges_items')->join('charges', function ($join) {
        $join->on('charges_items.charge_id', '=', 'charges.id')
                ->where(DB::raw('MONTH(charges.date_charge)'), '=', date('n'))
                ->where(DB::raw('MONTH(charges.deadline)'), '=', date('n'))
                ->whereNull('charges.date_payment');
    })->join('vat_types', function ($join) {
        $join->on('charges_items.vat_types_id', '=', 'vat_types.id');
    })->select(DB::raw('SUM(amount) as total, SUM(((amount * vat_types.value) / 100)) as mtva'))->first();
    ?>

    @if ($chargesMonth && $chargesMonth->total)
        <div class="ibox">
            <div class="ibox-title">
                <h5>Dépenses du mois</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{ number_format($chargesMonth ? $chargesMonth->total  : 0, 0, ',', '.') }}
                    €</h1>
                @if ($chargesMonthToPay && $chargesMonthToPay->total)
                    <div class="stat-percent font-bold text-navy">{{ number_format($chargesMonthToPay ? $chargesMonthToPay->total  : 0, 0, ',', '.') }}
                        €
                    </div>
                    <small>Reste dû</small>
                @endif
            </div>
        </div>
    @endif
@endif