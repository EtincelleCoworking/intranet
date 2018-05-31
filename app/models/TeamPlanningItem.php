<?php

/**
 * City Entity
 */
class TeamPlanningItem extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'team_planning_item';


    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

}