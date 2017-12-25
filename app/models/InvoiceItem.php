<?php

/**
 * InvoiceItem Model
 */
class InvoiceItem extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invoices_items';

    /**
     * The Fillable fields
     */
    protected $fillable = array('id', 'ressource_id', 'text', 'amount', 'vat_types_id');


    public function scopeTotalTVA($query)
    {
        return $query
            ->join('vat_types', function ($j) {
                $j->on('vat_types_id', '=', 'vat_types.id')->where('value', '>', 0);
            })
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
            })
            ->select(
                DB::raw('date_format(invoices.date_payment, "%Y-%m") as days'),
//    				DB::raw('CONCAT(YEAR(invoices.date_payment), "Q", QUARTER(invoices.date_payment)) as quarter'),
                'vat_types.value',
                DB::raw('SUM((invoices_items.amount * vat_types.value) / 100) as total')
            )
            ->groupBy('days', 'vat_types.value')
            ->orderBy('days', 'ASC')//->get()
            ;
    }


    public function scopeTotalPerMonth($query)
    {
        return $query
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
            })
            ->select(
                DB::raw('date_format(invoices.date_invoice, "%Y-%m") as period'),
                DB::raw('SUM(invoices_items.amount) as total')
            )
            ->groupBy('period')
            ->orderBy('period', 'DESC')//->get()
            ;
    }

    public function scopeTotal($query)
    {
        return $query
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')
                    ->where('type', '=', 'F');
            })
            ->where('invoices.date_invoice', '>=', date('Y-m-d', Config::get('etincelle.activity_period_started')))
            ->select(
                DB::raw('SUM(invoices_items.amount) as total')
            );
    }


    public function scopeByKind($query)
    {
        $query = $query
            ->join('ressources', 'ressource_id', '=', 'ressources.id', 'left outer')
            ->join('ressource_kind', 'ressources.ressource_kind_id', '=', 'ressource_kind.id', 'left outer')
            ->groupBy('ressource_kind.name')
            ->orderBy('ressource_kind.order_index', 'desc')
            ->addSelect('ressource_kind.name as kind');
        return $query;
    }

    public function scopeByLocation($query, $location_id)
    {
        if ($location_id) {
            $query
                ->join('users', 'subscription_user_id', '=', 'users.id', 'left outer')
                ->where(function ($query) use ($location_id) {
                    $query->where(function ($query) use ($location_id) {
                        $query->where('ressource_id', '=', Ressource::TYPE_COWORKING)
                            ->where('users.default_location_id', '=', $location_id);
                    })
                        ->orWhere(function ($query) use ($location_id) {
                            $query->where('ressource_id', '<>', Ressource::TYPE_COWORKING)
                                ->where('ressources.location_id', '=', $location_id);
                        });
                });
        }
        return $query;
    }

    public function scopeTotalCountPerMonth($query)
    {
        return $query
            ->join('invoices', function ($j) {
                $j->on('invoices_items.invoice_id', '=', 'invoices.id')
                    ->where('type', '=', 'F');
            })
            ->select(
                DB::raw('date_format(invoices.date_invoice, "%Y-%m") as period'),
                DB::raw('count(distinct(invoices_items.subscription_user_id)) as total')
            )
            ->groupBy('period')
            ->orderBy('period', 'DESC')//->get()
            ;
    }

    public function scopeWithoutStakeholders($query)
    {
        return $query
            ->join('organisations', 'organisation_id', '=', 'organisations.id', 'left outer')
            ->where(function ($query) {
                $query->where('organisations.is_founder', '=', false)
                    ->orWhereNull('organisation_id');
            });
    }

    public function scopeWithoutExceptionnals($query)
    {
        return $query
            ->join('ressources as scopeWithoutExceptionnals_ressources', 'ressource_id', '=', 'scopeWithoutExceptionnals_ressources.id', 'left outer')
            ->where(function ($query) {
                $query->where('scopeWithoutExceptionnals_ressources.ressource_kind_id', '<>', RessourceKind::TYPE_EXCEPTIONNAL);
            });
    }

    public function scopePending($query)
    {
        return $query
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')
                    ->where('type', '=', 'F');
            })
            ->select(
                DB::raw('SUM(invoices_items.amount) as total')
            )
            ->where('on_hold', '=', false)
            ->whereNull('date_payment')
            ->first();
    }

    public function scopeOnHold($query)
    {
        return $query
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')
                    ->where('type', '=', 'F');
            })
            ->select(
                DB::raw('SUM(invoices_items.amount) as total')
            )
            ->where('on_hold', '=', true)
            ->whereNull('date_payment')
            ->first();
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Invoice)
     */
    public function invoice()
    {
        return $this->belongsTo('Invoice');
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to VAT_Types)
     */
    public function vat()
    {
        return $this->belongsTo('VatType', 'vat_types_id');
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function ressource()
    {
        return $this->belongsTo('Ressource', 'ressource_id');
    }

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|min:1'
    );
}