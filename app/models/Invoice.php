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
		return $this->hasMany('InvoiceItem');
	}

	/**
	 * Identifier invoice
	 */
	public function getIdentAttribute()
	{
		return $this->type.$this->days.'-'.str_pad($this->number, 4, 0, STR_PAD_LEFT);
	}

	/**
	 * Total amount
	 */
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
		'user_id' => 'required|min:1',
		'organisation_id' => 'required|min:1',
	);
}