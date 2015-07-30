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
			->join('vat_types', function($j)
			{
				$j->on('vat_types_id', '=', 'vat_types.id')->where('value', '>', 0);
			})
			->join('invoices', function($j)
			{
				$j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
			})
			->select(
				DB::raw('date_format(invoices.date_payment, "%Y-%m") as days'),
//    				DB::raw('CONCAT(YEAR(invoices.date_payment), "Q", QUARTER(invoices.date_payment)) as quarter'),
				'vat_types.value',
				DB::raw('SUM((amount * vat_types.value) / 100) as total')
			)
			->groupBy('days', 'vat_types.value')
			->orderBy('days', 'ASC')
			//->get()
			;
	}


	public function scopeTotalPerMonth($query)
	{
		return $query
			->join('invoices', function($j)
			{
				$j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
			})
			->select(
				DB::raw('date_format(invoices.date_invoice, "%Y-%m") as period'),
				DB::raw('SUM(amount) as total')
			)
			->groupBy('period')
			->orderBy('period', 'DESC')
			//->get()
			;
	}


	public function scopeCoworking($query)
	{
		return $query
			->whereIn('ressource_id', array(1, 5))
			//->get()
			;
	}

	public function scopeRoomRental($query)
	{
		return $query
			->whereIn('ressource_id', array(2, 3, 4, 8))
			//->get()
			;
	}

	public function scopeOther($query)
	{
		return $query
			->whereNotIn('ressource_id', array(1, 2, 3, 4, 5, 8))
			//->get()
			;
	}


	public function scopeTotalPerMonthWithoutStakeholders($query)
	{
		return $query
			->join('invoices', function($j)
			{
				$j->on('invoice_id', '=', 'invoices.id')
					->where('type', '=', 'F')

				;
			})
			->select(
				DB::raw('date_format(invoices.date_invoice, "%Y-%m") as period'),
				DB::raw('SUM(amount) as total')
			)
			->where(function ($query) {
				$query->whereNotIn('organisation_id', array(1, 2))
					->orWhereNull('organisation_id');
			})

			->groupBy('period')
			->orderBy('period', 'DESC')
			//->get()
			;
	}

	public function scopeTotalCountPerMonthWithoutStakeholders($query)
	{
		return $query
			->join('invoices', function($j)
			{
				$j->on('invoice_id', '=', 'invoices.id')
					->where('type', '=', 'F')

				;
			})
			->select(
				DB::raw('date_format(invoices.date_invoice, "%Y-%m") as period'),
				DB::raw('count(distinct(organisation_id)) as total')
			)
			->where(function ($query) {
				$query->whereNotIn('organisation_id', array(1, 2))
					->orWhereNull('organisation_id');
			})
			->groupBy('period')
			->orderBy('period', 'DESC')
			//->get()
			;
	}


	public function scopePending($query)
	{
		return $query
			->join('invoices', function($j)
			{
				$j->on('invoice_id', '=', 'invoices.id')
					->where('type', '=', 'F')

				;
			})
			->select(
				DB::raw('SUM(amount) as total')
			)
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
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'user_id' => 'required|min:1'
	);
}