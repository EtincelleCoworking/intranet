<?php

class PostboxNotification extends Eloquent
{
    protected $table = 'postbox_notification';

    public function reporter()
    {
        return $this->belongsTo('User', 'reporter_id');
    }

    public function organisation()
    {
        return $this->belongsTo('Organisation', 'organisation_id');
    }

    public function kind()
    {
        return $this->belongsTo('PostboxKind', 'kind_id');
    }

    public function items()
    {
        return $this->hasMany('PostboxItem')->orderBy('is_important', 'DESC');
    }

    public static $rules = array();

}