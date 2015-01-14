<?php
/**
* VatType Model
*/
class VatType extends Eloquent
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'vat_types';

	/**
	 * Relation BelongsTo (Vat_Types belongs to Invoices_Items)
	 */
	public function item()
	{
		return $this->belongsTo('InvoiceItem');
	}

	/**
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'user_id' => 'required|min:1'
	);

    /**
     * Get list of vat
     */
    public function scopeSelectAll($query)
    {
        $selectVals = $this->lists('value', 'id');
        return $selectVals;
    }
}