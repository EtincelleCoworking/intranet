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