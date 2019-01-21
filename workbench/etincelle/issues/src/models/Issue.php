<?php

class Issue extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'issues';

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required|exists:users',
        'title' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|exists:users',
        'title' => 'required|min:1'
    );

    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }
    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    public function scopeAll($query)
    {
        return $query;
    }

}