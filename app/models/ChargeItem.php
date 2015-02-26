<?php
/**
* Charge Item Entity
*/
class ChargeItem extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'charges_items';

    public function scopeTotalTVA($query)
    {
        return $query
                ->join('vat_types', function($j)
                    {
                        $j->on('vat_types_id', '=', 'vat_types.id')->where('value', '>', 0);
                    })
                ->join('charges', 'charge_id', '=', 'charges.id')
                ->select(
                    DB::raw('CONCAT(YEAR(charges.date_charge), "-", MONTH(charges.date_charge)) as days'),
                    'vat_types.value',
                    DB::raw('SUM((amount * vat_types.value) / 100) as total')
                )
                ->groupBy('days', 'vat_types.value')
                ->orderBy('days', 'ASC')
                ->get();
    }

    /**
     * Item belongs to Charge
     */
    public function charge()
    {
        return $this->belongsTo('Charge');
    }

    /**
     * Item belongs to Vat
     */
    public function vat()
    {
        return $this->belongsTo('VatType', 'vat_types_id');
    }
}