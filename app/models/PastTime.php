<?php
/**
* Past Time Entity
*/
class PastTime extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'past_times';

    /**
     * Past Time belongs to User
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Past Time belongs to Ressource
     */
    public function ressource()
    {
        return $this->belongsTo('Ressource');
    }

    /**
     * Past time total
     */
    public function getPastTimeAttribute()
    {
        if ($this->time_end) {
            $date1 = new DateTime($this->time_end);
            $date2 = new DateTime($this->time_start);
            $diff = $date2->diff($date1);

            if ($diff->i) {
                return $diff->h.' heure(s) '.$diff->i.' minute(s)';
            } else {
                return $diff->h.' heure(s)';
            }
        } else {
            return false;
        }
    }

    /**
     * Rules
     */
    public static $rules = array(
        'date_past' => 'required|min:1',
        'time_start' => 'min:5|max:5',
        'time_end' => 'min:5|max:5',
        'user_id' => 'required|exists:users,id'
    );
}