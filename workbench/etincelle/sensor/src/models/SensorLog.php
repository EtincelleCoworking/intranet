<?php

class SensorLog extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sensor_logs';

    public $timestamps = false;

    /**
     * Rules
     */
    public static $rules = array(
        'sensor_id' => 'required|exists:sensors'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'sensor_id' => 'required|exists:sensors'
    );


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function sensor()
    {
        return $this->belongsTo('Sensor');
    }

}