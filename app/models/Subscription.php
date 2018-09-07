<?php

class Subscription extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscription';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    /**
     * @return SubscriptionKind
     */
    public function kind()
    {
        return $this->belongsTo('SubscriptionKind', 'subscription_kind_id');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required|min:1',
        'organisation_id' => 'required|min:1',
        'subscription_kind_id' => 'required|min:1',
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|min:1',
        'organisation_id' => 'required|min:1',
        'subscription_kind_id' => 'required|min:1',
    );

    public function getDaysBeforeRenewAttribute()
    {
        $date1 = new DateTime();
        $date2 = new DateTime($this->renew_at);
        $diff = $date1->diff($date2);
        $result = $diff->days;
        if ($diff->invert) {
            $result = -1 * $result;
        }
        return $result;

    }

    public function getCaptionAttribute()
    {
        return $this->kind->name;
        //return sprintf('%s - %s', $this->kind->name, $this->user->fullname);

    }


    public function scopeTotalPerMonth($query)
    {
        return $query
            ->join('subscription_kind', 'subscription_kind_id', '=', 'subscription_kind.id')
            ->select(
                DB::raw('date_format(renew_at, "%Y-%m") as period'),
                DB::raw('SUM(subscription_kind.price) as total')
            )
            ->groupBy('period')
            ->orderBy('period', 'ASC');
    }

    public static function getActiveSubscriptionInfos()
    {
        $params = array();
        $active_subscription = InvoiceItem::where('ressource_id', Ressource::TYPE_COWORKING)
            ->where('invoices_items.subscription_user_id', Auth::user()->id)
            ->where('subscription_from', '<=', date('Y-m-d'))
            ->where('subscription_to', '>', date('Y-m-d'))
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
            })
            ->first();

        $params['active_subscription'] = $active_subscription;

        if ($active_subscription) {
            $params['subscription_used'] = PastTime::recap(Auth::user()->id, $active_subscription->subscription_from, $active_subscription->subscription_to, Ressource::TYPE_COWORKING, false)->first();
        } else {
            $params['subscription_used'] = array('hours' => 0, 'minutes' => 0);
        }
        if ($active_subscription && $params['subscription_used']) {
            $params['subscription_ratio'] = round(100 * ($params['subscription_used']->hours + $params['subscription_used']->minutes / 60) / $active_subscription->subscription_hours_quota);
        } else {
            $params['subscription_ratio'] = 0;
        }
        return $params;
    }

    public function formattedName()
    {
        return str_replace(array('%OrganisationName%', '%UserName%'), array($this->organisation->name, $this->user->fullname), $this->kind->name);
    }

//    /**
//     * Get list of vat
//     */
//    public function scopeSelectAll($query)
//    {
//        $selectVals = $this->orderBy('value', 'DESC')->lists('value', 'id');
//        return $selectVals;
//    }

    public function scopeSelectPrivateOffices($query)
    {
        $result = array(0 => '-');
        $data = $query
            ->select('subscription.*')
            ->join('subscription_kind', 'subscription_kind_id', '=', 'subscription_kind.id')
            ->join('ressources', 'ressource_id', '=', 'ressources.id')
            ->where('ressources.ressource_kind_id', RessourceKind::TYPE_PRIVATE_OFFICE)
            ->orderBy('subscription_kind_id', 'ASC')->get();
        foreach ($data as $item) {
            $result[$item->id] = $item->formattedName();
        }
        return $result;
    }

    public function renew()
    {
        $invoice = new Invoice();
        $invoice->type = 'F';
        $invoice->organisation_id = $this->organisation_id;
        if ($this->organisation->accountant_id) {
            $invoice->user_id = $this->organisation->accountant_id;
        } else {
            $invoice->user_id = $this->user_id;
        }
        $organisation = $this->organisation;
        if ($organisation->tva_number && ($organisation->country_id != Country::FRANCE)) {
            $invoice->details = sprintf('N° TVA Intracommunautaire: %s', $organisation->tva_number);
            $vat_types_id = VatType::whereValue(0)->first()->id;
        } else {
            $vat_types_id = VatType::whereValue(20)->first()->id;
        }
        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $this->organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();

        $invoice_line = new InvoiceItem();
        $invoice_line->invoice_id = $invoice->id;
        $invoice_line->ressource_id = $this->kind->ressource_id;
        $invoice_line->amount = $this->kind->price;
        $date = new \DateTime($this->renew_at);
        $date2 = new \DateTime($this->renew_at);
        $date2->modify('+' . $this->kind->duration);
        if ($this->kind->ressource_id == Ressource::TYPE_COWORKING) {
            $invoice_line->subscription_from = $date->format('Y-m-d');
            $invoice_line->subscription_to = $date2->format('Y-m-d');
            $invoice_line->subscription_hours_quota = $this->kind->hours_quota;
            $invoice_line->subscription_user_id = $this->user_id;
        }
        $date2->modify('-1 day');
        $invoice_line->text = sprintf("%s<br />\nDu %s au %s", $this->formattedName(), $date->format('d/m/Y'), $date2->format('d/m/Y'));
        $invoice_line->vat_types_id = $vat_types_id;
        $invoice_line->order_index = 1;
        $invoice_line->save();

        if ($this->kind->ressource_id == Ressource::TYPE_COWORKING && $this->user->is_student) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = $this->kind->ressource_id;
            $invoice_line->amount = -0.2 * $this->kind->price;
            $invoice_line->text = 'Réduction commerciale étudiant (-20%)';
            $invoice_line->vat_types_id = $vat_types_id;
            $invoice_line->order_index = 2;
            $invoice_line->save();
        }

        $date = new DateTime($this->renew_at);
        $date->modify('+' . $this->kind->duration);
        $this->renew_at = $date->format('Y-m-d');
        $this->reminded_at = null;

        $this->save();

        return $invoice;
    }

}