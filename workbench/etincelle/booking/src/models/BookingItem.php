<?php

class BookingItem extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking_item';

    public function ressource()
    {
        return $this->belongsTo('Ressource');
    }

    public function booking()
    {
        return $this->belongsTo('Booking');
    }

    public function scopeAll($query)
    {
        return $query;
    }

    public function toJsonEvent()
    {
        if (is_object($this->start_at)) {
            $start = $this->start_at;
        } else {
            $start = new \DateTime($this->start_at);

        }
        $end = clone $start;
        $end->modify(sprintf('+%d minutes', $this->duration));

        $user = Auth::user();
        $start2 = clone $start;
        $start2->modify('-2 days');
        $canManage = $user->isSuperAdmin() ||
            (($this->booking->user_id == $user->id) && ($start2->format('Y-m-d') >= date('Y-m-d')));

        $ofuscated_title = $this->booking->title;
        if (!$user->isSuperAdmin() && ($this->booking->user_id != $user->id)) {
            $ofuscated_title = 'Réservé';
            $className = sprintf('booking-ofuscated-%d', $this->ressource_id);
        }else{
            $className = 'booking';
        }

        return array(
            'title' => $ofuscated_title,
            'start' => $start->format('Y-m-d\TH:i:00'),
            'end' => $end->format('Y-m-d\TH:i:00'),
            'booking_id' => $this->booking->id,
            'id' => $this->id,
            'canDelete' => (bool)$canManage,
            'editable' => (bool)$canManage,
            'backgroundColor' => $this->ressource->booking_background_color,
            'color' => $this->ressource->booking_text_color,
            'location' => $this->ressource->name,
            'className' => $className
        );
    }
}