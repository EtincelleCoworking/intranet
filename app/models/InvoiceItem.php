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
    				DB::raw('CONCAT(YEAR(invoices.date_invoice), "-", MONTH(invoices.date_invoice)) as days'),
    				'vat_types.value',
    				DB::raw('SUM((amount * vat_types.value) / 100) as total')
    			)
    			->groupBy('days', 'vat_types.value')
    			->orderBy('days', 'ASC')
    			->get();
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