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

    <div class="widget style2 blue-bg">
        <div class="row">
            {{--
                        <div class="col-xs-4">
                            <i class="fa fa-money fa-5x"></i>
                        </div>
            --}}
            <div class="{{-- col-xs-8 --}}text-center">
                <span> CA du mois </span>

                <h2 class="font-bold">
                    {{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }}&nbsp;€</h2>
                <small>&nbsp;</small>
            </div>
        </div>
    </div>
@endif
