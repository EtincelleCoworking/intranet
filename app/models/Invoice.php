<?php

/**
 * Invoice Model
 */
class Invoice extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    public function scopeInvoiceOnly($query)
    {
        return $query->whereType('F')->whereNull('date_canceled');
    }

    public function scopeQuoteOnly($query, $filtre)
    {
        if ($filtre == 'canceled') {
            return $query->whereType('D')->whereNotNull('date_canceled');
        } else {
            return $query->whereType('D')->whereNull('date_canceled');
        }
    }

    public function scopeQuoteCanceled($query)
    {
        return $query->whereNotNull('date_canceled');
    }

    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Relation BelongsTo (Invoices belongs to Organisation)
     */
    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    /**
     * Relation One To Many (Invoice has many Invoices_Items)
     */
    public function items()
    {
        return $this->hasMany('InvoiceItem')->orderBy('order_index', 'ASC');;
    }

    /**
     * Relation One To Many (Invoice has many Invoices_Items)
     */
    public function comments()
    {
        return $this->hasMany('InvoiceComment');
    }

    /**
     * Identifier invoice
     */
    public function getIdentAttribute()
    {
        return $this->type . $this->days . '-' . str_pad($this->number, 4, 0, STR_PAD_LEFT);
    }

    /**
     * Identifier invoice
     */
    public function getCaptionAttribute()
    {
        if ($this->user) {
            return $this->ident . ' ' . $this->user->fullname;
        }
        return $this->ident;
    }

    /**
     * Days before deadline
     */
    public function getDaysDeadlineAttribute()
    {
        if ($this->deadline >= date('Y-m-d')) {
            $date1 = new DateTime($this->deadline);
            $date2 = new DateTime();
            $diff = $date2->diff($date1);

            return $diff->days;
        } else {
            return -1;
        }
    }

    /**
     * Total
     */
    public function getTotalAttribute()
    {
        $total = 0;

        if ($this->items) {
            /** @var InvoiceItem $value */
            foreach ($this->items as $key => $value) {
                $total += $value->amount;
            }
        }

        return sprintf('%0.2f', $total);
    }

    public function getStripeFeesAttribute()
    {
        $total = 0;

        /** @var InvoiceItem $value */
        foreach ($this->items as $key => $value) {
            $total += $value->amount * (1 + $value->vat->value / 100);
        }

        return sprintf('%0.2f', $total ? 0.25 + 1.8 / 100 * $total:0 );
    }

    /**
     * Total amount
     */
    public function scopeTotalInvoice($query, $items)
    {
        $total = 0;

        if ($items) {
            foreach ($items as $key => $value) {
                $total += $value->amount;
            }
        }

        return sprintf('%0.2f', $total);
    }

    /**
     * Total amount
     */
    public function scopeTotalInvoiceWithTaxes($query, $items)
    {
        $total = 0;

        if ($items) {
            foreach ($items as $key => $value) {
                $total += $value->amount * (1 + $value->vat->value / 100);
            }
        }

        return sprintf('%0.2f', $total);
    }

    /**
     * Get next invoice number
     */
    static public function next_invoice_number($type, $days)
    {
        $last = Invoice::where('type', $type)->where('days', $days)->orderBy('number', 'DESC')->first();
        if ($last) {
            return ($last->number + 1);
        } else {
            return 1;
        }
    }

    /**
     * Rules
     */
    public static $rules = array(
        'date_invoice' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'address' => 'required|min:1'
    );

    /**
     * Get list of users
     */
    public function scopeSelect($query, $title = "Select")
    {
        $selectVals[''] = $title;
        $selectVals += $this->orderBy('date_invoice', 'desc')->orderBy('number', 'desc')->where('type', 'F')->get()->lists('caption', 'id');
        return $selectVals;
    }
}
