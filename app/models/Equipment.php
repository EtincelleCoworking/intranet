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

    public function isUp()
    {
        return (time() - strtotime($this->last_seen_at)) < 5 * $this->frequency;
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
                foreach(array('CYAN' => '#3BF8F8', 'MAGENTA' => '#F853F8', 'YELLOW' => '#F6F732', 'BLACK' => '#000000') as $color_name=> $color_hex){
                    if(isset($d[$color_name])){
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