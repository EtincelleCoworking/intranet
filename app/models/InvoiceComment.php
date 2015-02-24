<?php
/**
* InvoiceComment Model
*/
class InvoiceComment extends Eloquent
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'invoices_comments';

    /**
     * The Fillable fields
     */
    protected $fillable = array('id', 'user_id', 'invoice_id', 'content');

	/**
	 * Relation BelongsTo (Invoices_Comments belongs to Invoice)
	 */
	public function invoice()
	{
		return $this->belongsTo('Invoice');
	}

	/**
	 * Relation BelongsTo (Invoices_Comments belongs to User)
	 */
	public function user()
	{
		return $this->belongsTo('User');
	}

	/**
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'user_id' => 'required|min:1',
		'invoice_id' => 'required|min:1',
		'content' => 'required|min:1'
	);
}