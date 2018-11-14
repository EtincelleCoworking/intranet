<?php

class BookingItem extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking_item';

    /**
     * Rules
     */
    public static $rules = array(
        'ressource_id' => 'required|exists:ressource',
        'booking_id' => 'required|exists:booking',
        'start_at' => 'date|unique_booking',
        'duration' => 'required|min:15'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'ressource_id' => 'required|exists:ressource',
        'booking_id' => 'required|exists:booking',
        'start_at' => 'date|unique_booking',
        'duration' => 'required|min:15'
    );

    public function ressource()
    {
        return $this->belongsTo('Ressource');
    }

    public function booking()
    {
        return $this->belongsTo('Booking');
    }

    public function invoice()
    {
        return $this->belongsTo('Invoice');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo('User');
    }

    public function members()
    {
        return $this->belongsToMany('User', 'booking_item_user', 'booking_item_id', 'users_id');
    }

    public function scopeAll($query)
    {
        return $query;
    }

    public function isMember($user_id)
    {
        foreach ($this->members as $member) {
            if ($user_id == $member->id) {
                return true;
            }
        }
        return false;
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

        $className = sprintf('booking-%d', $this->ressource_id);

        $ofuscated_title = $this->booking->title;
        if ($this->booking->is_private && !$user->isSuperAdmin() && ($this->booking->user_id != $user->id)) {
            $ofuscated_title = 'Réservé';
            $className .= sprintf(' booking-ofuscated-%d', $this->ressource_id);
        } else {
            $className .= ' booking';
        }
        $backgroundColor = $this->ressource->booking_background_color;
        $borderColor = adjustBrightness($this->ressource->booking_background_color, -32);
        $textColor = adjustBrightness($this->ressource->booking_background_color, -128);
        if ($end->format('Y-m-d H:i:s') < date('Y-m-d H:i:s')) {
            $backgroundColor = hexColorToRgbWithTransparency($backgroundColor, '0.4');
            $borderColor = hexColorToRgbWithTransparency($borderColor, '0.4');
            $textColor = hexColorToRgbWithTransparency($textColor, '0.4');

            $className .= ' booking-completed';
        }

        if($this->confirmed_at){
            $className .= ' booking-confirmed';
        }else{
            $className .= ' booking-not-confirmed';
        }

        if ($user->isSuperAdmin()) {
            $time = new PastTime();
            $time->user_id = $this->booking->user_id;
            $time->ressource_id = $this->ressource_id;
            $time->date_past = $this->start_at;
            $time->time_start = $this->start_at;
            $time->time_end = strtotime(sprintf('+ %d minutes', $this->duration), is_object($this->start_at) ? $this->start_at->getTimestamp() : strtotime($this->start_at));

            $is_accounted = PastTime::query()
                    ->where('user_id', $time->user_id)
                    ->where('ressource_id', $time->ressource_id)
                    ->where('date_past', $time->date_past)
                    ->where('time_start', $time->time_start)
                    ->where('time_end', $time->time_end)
                    ->count() > 0;
        } else {
            $is_accounted = false;
        }

        return array(
            'title' => $ofuscated_title,
            'start' => $start->format('c'),
            'end' => $end->format('c'),
            'booking_id' => $this->booking->id,
            'id' => $this->id,
            'user_id' => $this->booking->user_id,
            'is_private' => (bool)$this->booking->is_private,
            'is_accounted' => (bool)$is_accounted,
            'is_confirmed' => (bool)($this->confirmed_at != null),
            'is_open_to_registration' => (bool)$this->is_open_to_registration,
            'description' => (string)$this->booking->content,
            'canDelete' => (bool)$canManage,
            'editable' => (bool)$canManage,
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
            'textColor' => $textColor,
            'ressource_id' => $this->ressource->id,
            'resourceId' => 'res'.$this->ressource->id,
            'className' => $className,
            'duration' => $this->duration
        );
    }


    public function checkBeDeletedBy($user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if ($this->booking->user_id == $user->id) {
            $start = new \DateTime($this->start_at);
            $start2 = clone $start;
            $start2->modify('-2 days');
            if ($start2->format('Y-m-d H:i:s') <= date('Y-m-d H:i:s')) {
                throw new \Exception(sprintf('Cette réservation ne peut pas être annulée (%s - %s)', $start2->format('Y-m-d H:i:s'), date('Y-m-d H:i:s')));
            }
        } else {
            throw new \Exception('Réservation inconnue');
        }
    }

    static function getWifiHtml($location, $room, $day, $meeting_title, $wifi_login, $wifi_password, $timerange){
        $html = <<<EOS
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
    <title>%location% - %room% - %day%</title>
    <style type="text/css">
    .header {
/*
        position: absolute;
        right: 0;
        top: 0;
        left: 0;
*/
        padding: 1rem;
        background-color: #efefef;
        text-align: left;
    }
    .footer {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 1rem;
        background-color: #efefef;
        text-align: right;
    }
</style>
</head>
<body>
<div class="header">
    <img src="http://www.coworking-toulouse.com/wp-content/uploads/2015/04/etincelle-coworking-400x400.gif" height="85" width="85" style="float: right" />
    <h1>%title%</h1>
</div>
<div class="page">
    <h2>Bienvenue chez Etincelle Coworking</h2>
    <p>Pour vous connecter au WIFI:
    <ol>
        <li>Sélectionnez le réseau "EtincelleCoworking" (réseau ouvert, sans mot de passe) </li>
        <li>Une page d’identification devrait s’afficher avec le logo Etincelle Coworking. Si ce n’est pas le cas, ouvrez un navigateur internet et allez à l’adresse http://192.168.2.1:8000/</li>
        <li>Utilisez les informations de connexion ci-dessous en respectant les majuscules et les minuscules.</li>
    </ol></p>
    <table>
        <tr>
            <td style="font-size: 18pt">Identifiant&nbsp;:&nbsp;</td>
            <td style="font-size: 18pt">%wifi_login%</td>
        </tr>
        <tr>
            <td style="font-size: 18pt">Mot de passe&nbsp;:&nbsp;</td>
            <td style="font-size: 18pt">%wifi_password%</td>
        </tr>
    </table>
    
    <p>NB: Cet accès est valable aujourd'hui uniquement (%day%).</p>
    <p>Si vous avez besoin d'aide, contactez un membre de l'équipe dans la zone d’accueil ou au 05 64 88 01 30 (renvoyé sur nos téléphones portables).</p>
    
    <p>&nbsp;</p>
    <p>Si tout fonctionne correctement, vous pouvez : 
    <ul>
        <li>Aimer notre page Facebook : http://fb.me/EtincelleCoworking</li>
        <li>Nous suivre sur Twitter : https://twitter.com/etincelle_tls</li>
        <li>Laisser un avis sur Google : https://goo.gl/vzeXYy</li>
    </ul>
    </p>
    <p>&nbsp;</p>
    <p>Nous vous souhaitons une excellente réunion!</p>
</div>
<div class="footer">
    <small>%room% - %day% %timeslot%</small>
</div>
<div class="page-break"></div>
</body></html>
EOS;
        $macros = array(
            '%location%' => $location,
            '%room%' => $room,
            '%day%' => date('d/m/Y', strtotime($day)),
            '%title%' => $meeting_title,
            '%wifi_login%' => $wifi_login,
            '%wifi_password%' => $wifi_password,
            '%timeslot%' => $timerange,
        );
        return str_replace(array_keys($macros), array_values($macros), $html);
    }
}
