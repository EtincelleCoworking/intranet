<?php

/**
 * Organisation Model
 */
class Organisation extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'organisations';

    /**
     * The guarded fields
     */
    protected $guarded = array('id');

    /**
     * Relation Belongs To Many (Organisation has many Users)
     */
    public function users()
    {
        return $this->belongsToMany('User', 'organisation_user', 'organisation_id', 'user_id');
    }

    /**
     * Relation BelongsTo (Organisation belongs to country)
     */
    public function country()
    {
        return $this->belongsTo('Country');
    }

    public function accountant()
    {
        return $this->belongsTo('User');
    }

    public function rules()
    {
        return $this->hasMany('InvoicingRule')
            ->orderBy('order_index', 'ASC');
    }

    public function domiciliation_kind()
    {
        return $this->belongsTo('DomiciliationKind');
    }

    public function getDomiciliationFrequency()
    {
        if (null != $this->domiciliation_kind) {
            if ('Domiciliation commerciale' != $this->domiciliation_kind->name) {
                return str_replace('Domiciliation commerciale avec renvoi de courrier ', '', $this->domiciliation_kind->name);
            }
        }
        return '-';
    }

    /**
     * Organisation has many invoices
     */
    public function invoices()
    {
        return $this->hasMany('Invoice')->orderBy('date_invoice', 'DESC');
    }

    /**
     * Organisation has many charges
     */
    public function charges()
    {
        return $this->hasMany('Charge');
    }

    /**
     * Full address
     */
    public function getFulladdressAttribute()
    {
        return $this->name . "\r\n" . $this->address . "\r\n" . $this->zipcode . ' ' . $this->city . "\r\n" . $this->country->name;
    }

    /**
     * Rules
     */
    public static $rules = array(
        'name' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'name' => 'required|min:1|unique:organisations'
    );

    /**
     * Get list of organisations where user is not in
     */
    public function scopeSelectNotInOrganisation($query, $user, $title = "Select")
    {
        $ids = OrganisationUser::where('user_id', $user)->lists('organisation_id');
        $selectVals = array();
        if ($title) {
            $selectVals[''] = $title;
        }
        if ($ids) {
            $selectVals += $this->whereNotIn('id', $ids)->orderBy('name', 'asc')->lists('name', 'id');
        } else {
            $selectVals += $this->orderBy('name', 'asc')->lists('name', 'id');
        }
        return $selectVals;
    }


    /**
     * Get list of organisations
     */
    public function scopeSelectAll($query, $title = "Select")
    {
        $selectVals = array();
        if ($title) {
            $selectVals[''] = $title;
        }
        $selectVals += $this->orderBy('name', 'ASC')->get()->lists('name', 'id');
        return $selectVals;
    }

    public function scopeDomiciliation($query)
    {
        return $query->where('is_domiciliation', 1);
    }

    public function getNotYetCountedBookingCount($period_start, $period_end)
    {
        $sql = 'select count(booking_item.id) as cnt
from booking_item 
join booking on booking_item.booking_id = booking.id
LEFT OUTER JOIN past_times 
  ON past_times.user_id = booking.user_id 
    AND past_times.ressource_id = booking_item.ressource_id
    AND past_times.date_past = DATE_FORMAT(booking_item.start_at, "%Y-%m-%d")
    AND past_times.time_start = booking_item.start_at
    AND past_times.time_end = booking_item.start_at + INTERVAL booking_item.duration MINUTE
    AND past_times.is_free != true
WHERE past_times.invoice_id IS NULL
  AND past_times.id IS NULL
  AND booking_item.start_at BETWEEN "' . $period_start . '" AND "' . $period_end . '"
  AND booking.organisation_id = ' . $this->id;

        $items = DB::select(DB::raw($sql));
        return $items[0]->cnt;
    }


    public function getCountedBookingCount($period_start, $period_end)
    {
        $sql = 'select count(past_times.id) as cnt
from booking_item 
join booking on booking_item.booking_id = booking.id
LEFT OUTER JOIN past_times 
  ON past_times.user_id = booking.user_id 
    AND past_times.ressource_id = booking_item.ressource_id
    AND past_times.date_past = DATE_FORMAT(booking_item.start_at, "%Y-%m-%d")
    AND past_times.time_start = booking_item.start_at
    AND past_times.time_end = booking_item.start_at + INTERVAL booking_item.duration MINUTE
    AND past_times.is_free != true
WHERE (past_times.invoice_id = 0 OR past_times.invoice_id IS NULL)
  AND past_times.id IS NOT NULL
  AND booking_item.start_at BETWEEN "' . $period_start . '" AND "' . $period_end . '"
  AND booking.organisation_id = ' . $this->id;

        $items = DB::select(DB::raw($sql));
        return $items[0]->cnt;
    }

    public function applyInvoicingRulesAndSaveLines_Quotes($invoice_lines)
    {
        foreach ($this->rules as $rule) {
            $processor = $rule->createProcessor();
            if ($processor && $processor->isValidForQuotes()) {
                $invoice_lines = $processor->execute($invoice_lines, array());
            }
        }
        $order_index = 1;
        foreach ($invoice_lines as $line) {
            $line->order_index = $order_index++;
            $line->save();
        }
    }

    public function applyInvoicingRulesAndSaveLines_Invoices($invoice_lines, $invoice_lines_details)
    {
        foreach ($this->rules as $rule) {
            $processor = $rule->createProcessor();
            if ($processor && $processor->isValidForInvoices()) {
                $invoice_lines = $processor->execute($invoice_lines, $invoice_lines_details);
            }
        }
        $order_index = 1;
        foreach ($invoice_lines as $line) {
            $line->order_index = $order_index++;
            $line->save();
        }
    }
}