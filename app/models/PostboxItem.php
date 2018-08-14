<?php

class PostboxItem extends Eloquent
{
    protected $table = 'postbox_item';

    public function notification()
    {
        return $this->belongsTo('PostboxNotification', 'postbox_notification_id');
    }

    public function kind()
    {
        return $this->belongsTo('PostboxKind', 'kind_id');
    }

    public static $rules = array();

    public function getContentFmt()
    {
        $result = '';
        if ($this->is_important) {
            $result .= '(Recommandé) ';
        }
        $result .= sprintf('%d %s', $this->quantity, $this->kind->name);
        if (!empty($this->from_name)) {
            $result .= sprintf(' - Expéditeur : %s', $this->from_name);
        }
        if (!empty($this->details)) {
            $result .= sprintf(' (%s)', $this->details);
        }
        return $result;
    }
}