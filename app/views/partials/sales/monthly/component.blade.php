@if(Auth::user()->isSuperAdmin())
    <?php

    if (empty($target_period)) {
        $target_days = date('Ym');
    } else {
        $target_days = substr($target_period, 0, 4) . substr($target_period, 5, 2);
    }
    $totalMonth = DB::table('invoices_items')->join('invoices', function ($join) use ($target_days) {
        if (Auth::user()->isSuperAdmin()) {
            $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.type', '=', 'F')
                ->where('invoices.days', '=', $target_days);
        } else {
            $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.type', '=', 'F')
                ->where('invoices.user_id', '=', Auth::id())
                ->where('invoices.days', '=', $target_days);
        }
    })->join('ressources', 'ressources.id', '=', 'invoices_items.ressource_id')
        ->where('ressources.ressource_kind_id', '!=', RessourceKind::TYPE_EXCEPTIONNAL)
        ->select(DB::raw('SUM(invoices_items.amount) as total'))->groupBy('invoices.days')->first();

    ?>
    <div class="ibox">
        <div class="ibox-content">
            <h5>CA du mois</h5>
            <h1 class="no-margins">
                {{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }}&nbsp;€
            </h1>
        </div>
    </div>
@endif
