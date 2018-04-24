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

    protected function renderInkLevel($colorMap, $data)
    {
        $result = '';
        foreach ($colorMap as $color_name => $color_hex) {
            if (isset($data[$color_name]['status']) && ($data[$color_name]['status'] >= 0)) {
                $result .= sprintf('<div class="progress" style="margin-bottom: 5px">
                                <div style="width: %1$d%%; background-color: %2$s" aria-valuemax="100" aria-valuemin="0" aria-valuenow="%1$d" role="progressbar" class="progress-bar">
                                    %1$d%%
                                </div>
                            </div>', $data[$color_name]['status'], $color_hex);
            }
        }
        return $result;
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
            case 'Canon':
                $result .= $this->renderInkLevel(array('cyan' => '#3BF8F8', 'magenta' => '#F853F8', 'yellow' => '#F6F732', 'black' => '#000000'), $d);
                break;
            default:
                $result .= '<pre>';
                $result .= print_r($d, true);
                $result .= '</pre>';
                break;
        }
        return $result;
    }

    public function storeData($data)
    {
        switch ($this->kind) {
            case 'Oki':
            case 'Canon':
                $new_data = array(
                    'cyan' => array(
                        'status' => -1,
                        'notified_at' => null,
                    ),
                    'magenta' => array(
                        'status' => -1,
                        'notified_at' => null,
                    ),
                    'yellow' => array(
                        'status' => -1,
                        'notified_at' => null,
                    ),
                    'black' => array(
                        'status' => -1,
                        'notified_at' => null,
                    ),
                );
                $existing_data = json_decode($this->data, true);
                if (is_array($existing_data)) {
                    foreach ($existing_data as $color_name => $color_data) {
                        if (isset($data[$color_name])) {
                            if ($data[$color_name] > 90) {
                                $new_data[$color_name]['notified_at'] = null;
                            } elseif (($data[$color_name] < 30) && empty($existing_data[$color_name]['notified_at'])) {
                                $slack_message = array(
                                    'text' => sprintf('La cartouche *%s* de l\'imprimante %s arrive à un niveau bas (%d%%) à %s',
                                        $color_name, $this->kind, $data[$color_name], $this->location->fullName)
                                );
                                $this->slack(Config::get('etincelle.slack_staff_toulouse'), $slack_message);
                                $new_data[$color_name]['notified_at'] = date('Y-m-d H:i:s');
                            }
                            $new_data[$color_name]['status'] = $data[$color_name];
                        }
                    }
                } else {
                    foreach ($new_data as $color_name => $color_data) {
                        $new_data[$color_name]['status'] = $data[$color_name];
                    }
                }

                $this->data = json_encode($new_data, true);
                break;
            default:
                $this->data = json_encode($data, true);
                break;
        }
    }

    protected function slack($endpoint, $data)
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "payload=" . urlencode(json_encode($data)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        $errors = curl_error($ch);
        if ($errors) {
            Log::error($errors, array('context' => 'user.shown'));
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info(sprintf('Slack payload: %s', json_encode($data)), array('context' => 'user.shown'));
        Log::info(sprintf('Slack response (HTTP Code: %s): %s', $responseCode, $result), array('context' => 'user.shown'));
        curl_close($ch);

        return $result;
    }
}