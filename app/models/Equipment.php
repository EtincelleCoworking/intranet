<?php

/**
 * Equipment Entity
 */
class Equipment extends Eloquent
{

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     * PhoneBox belongs to User
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'ip' => 'required|ip',
        'location_id' => 'required',
    );

    public function getAge()
    {
        return (time() - strtotime($this->last_seen_at)) / $this->frequency;
    }

    protected function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 2);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public function lastSeenAgo()
    {
        return $this->time_elapsed_string($this->last_seen_at);
    }

    public function frequencyFmt()
    {
        return $this->secondsToTime($this->frequency);
    }

    public function getStatus()
    {
        $age = $this->getAge();
        if ($age < 2) {
            return 'good';
        }
        if ($age < 5) {
            return 'warning';
        }
        return 'danger';
    }

    protected function secondsToTime($inputSeconds)
    {
        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // Extract days
        $days = floor($inputSeconds / $secondsInADay);

        // Extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // Extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // Extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // Format and return
        $timeParts = [];
        $sections = [
            'day' => (int)$days,
            'hour' => (int)$hours,
            'minute' => (int)$minutes,
            'second' => (int)$seconds,
        ];

        foreach ($sections as $name => $value) {
            if ($value > 0) {
                $timeParts[] = $value . ' ' . $name . ($value == 1 ? '' : 's');
            }
        }

        return implode(', ', $timeParts);
    }

    public function dataFmt()
    {
        if (empty($this->data)) {
            return '';
        }
        $d = json_decode($this->data, true);
        if (count($d) == 0) {
            return '';
        }
        $result = '';
        switch ($this->kind) {
            case 'Oki':
                foreach (array('CYAN' => '#3BF8F8', 'MAGENTA' => '#F853F8', 'YELLOW' => '#F6F732', 'BLACK' => '#000000') as $color_name => $color_hex) {
                    if (isset($d[$color_name])) {
                        $result .= sprintf('<div class="progress" style="margin-bottom: 5px">
                                <div style="width: %1$d%%; background-color: %2$s" aria-valuemax="100" aria-valuemin="0" aria-valuenow="%1$d" role="progressbar" class="progress-bar">
                                    <span class="sr-only">%1$d%% </span>
                                </div>
                            </div>', $d[$color_name], $color_hex);
                    }
                }
                break;
            default:
                $result .= '<pre>';
                $result .= print_r($d, true);
                $result .= '</pre>';
                break;
        }
        return $result;
    }
}