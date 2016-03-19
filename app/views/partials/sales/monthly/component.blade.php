@if(Auth::user()->isSuperAdmin())
    <?php
    $totalMonth = DB::table('invoices_items')->join('invoices', function ($join) {
        if (Auth::user()->isSuperAdmin()) {
            $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.type', '=', 'F')
                    ->where('invoices.days', '=', date('Ym'));
        } else {
            $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                    ->where('invoices.type', '=', 'F')
                    ->where('invoices.user_id', '=', Auth::id())
                    ->where('invoices.days', '=', date('Ym'));
        }
    })->select(DB::raw('SUM(amount) as total'))->groupBy('invoices.days')->first();

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
