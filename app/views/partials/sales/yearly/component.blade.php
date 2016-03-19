@if(Auth::user()->isSuperAdmin())
    <?php
    $sales = DB::table('invoices_items')->join('invoices', function ($join) {
        $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.type', '=', 'F')
                ->where('invoices.on_hold', '=', false)
                ->where('invoices.date_invoice', '>=', date('Y-m-d', Config::get('etincelle.activity_started')));
    })->select(DB::raw('SUM(amount) as total'))->first();

    ?>

    <div class="widget style2 navy-bg">
        <div class="row">
            {{--
                        <div class="col-xs-4">
                            <i class="fa fa-money fa-5x"></i>
                        </div>
                        --}}
            <div class="{{--col-xs-8--}} text-center">
                <span> CA<small> (hors en compte)</small></span>

                <h2 class="font-bold">
                    {{ number_format($sales ? $sales->total : 0, 0, ',', '.') }}&nbsp;€
                </h2>
                <small> {{  number_format(31 * 24 * 3600 * $sales->total / (strtotime('now') - Config::get('etincelle.activity_started')), 0, ',', '.') }}
                    € /mois
                </small>
            </div>
        </div>
    </div>
@endif
