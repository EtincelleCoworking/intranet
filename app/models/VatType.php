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
     * Rules
     */
    public static $rules = array(
        'value' => 'required|min:1'
    );

	/**
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'value' => 'required|min:1'
	);

    /**
     * Get list of vat
     */
    public function scopeSelectAll($query)
    {
        $selectVals = $this->orderBy('value', 'DESC')->lists('value', 'id');
        return $selectVals;
    }
}