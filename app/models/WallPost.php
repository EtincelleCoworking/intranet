<?php

class WallPost extends \Gzero\EloquentTree\Model\Tree{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wall_posts';

    /**
     * Relation BelongsTo (Vat_Types belongs to Invoices_Items)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'message' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'message' => 'required|min:1'
    );

    public function getCreatedAttribute()
    {
        $time = strtotime($this->created_at);
        $d = new \DateTime($this->created_at);

        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $months = ['Janvier', 'Février', 'Mars', 'Avril',' Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        if ($time > strtotime('-2 minutes'))
        {
            return 'Il y a quelques secondes';
        }
        elseif ($time > strtotime('-30 minutes'))
        {
            return 'Il y a ' . floor((strtotime('now') - $time)/60) . ' min';
        }
        elseif ($time > strtotime('today'))
        {
            return $d->format('G:i');
        }
        elseif ($time > strtotime('yesterday'))
        {
            return 'Hier, ' . $d->format('G:i');
        }
        elseif ($time > strtotime('this week'))
        {
            return $weekDays[$d->format('N') - 1] . ', ' . $d->format('G:i');
        }
        else
        {
            return $d->format('j') . ' ' . $months[$d->format('n') - 1] . ', ' . $d->format('G:i');
        }
    }

//    /**
//     * Get list of vat
//     */
//    public function scopeSelectAll($query)
//    {
//        $selectVals = $this->orderBy('value', 'DESC')->lists('value', 'id');
//        return $selectVals;
//    }
}