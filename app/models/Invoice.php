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

	/**
	 * Relation BelongsTo (Invoices belongs to User)
	 */
	public function user()
	{
		return $this->belongsTo('User');
	}

	/**
	 * Relation One To Many (Invoice has many Invoices_Items)
	 */
	public function items()
	{
		return $this->hasMany('InvoiceItem');
	}

	public function scopeTotalInvoice($query, $items) {
		$total = 0;
		
		if ($items) {
			foreach ($items as $key => $value) {
				$total += $value->amount;
			}
		}
		
		return sprintf('%0.2f', $total);
	}

	/**
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'user_id' => 'required|min:1'
	);
}