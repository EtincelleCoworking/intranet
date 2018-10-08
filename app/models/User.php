<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{
    const LEAD_STATUS_NEW = 1;
    const LEAD_STATUS_SCHEDULED = 2;
    const LEAD_STATUS_VISITED = 3;
    const LEAD_STATUS_TRIED = 4;
    const LEAD_STATUS_CLOSED = 5;

    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token', 'email', 'pivot');

    /**
     * The guarded fields
     */
    protected $guarded = array('id');

    /**
     * Rules
     */
    public static $rules = array(
        'email' => 'required|email',
        'firstname' => 'required',
        'lastname' => 'required',
        'password' => 'min:5',
        'personnal_code' => 'regex:/[0-9]{6}/',
        'avatar' => 'image'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'email' => 'required|email|unique:users',
        'firstname' => 'required',
        'lastname' => 'required',
        'password' => 'required|min:5',
        'avatar' => 'image'
    );

    /**
     * Relation One To Many (User has many Invoices)
     */
    public function invoices()
    {
        return $this->hasMany('Invoice');
    }

    public function hashtags()
    {
        return $this->belongsToMany('Hashtag', 'user_hashtag', 'user_id', 'hashtag_id');
    }

    public function affiliateUser()
    {
        return $this->belongsTo('User', 'affiliate_user_id');
    }

    /**
     * Relation One To Many (User has many Invoices)
     */
    public function devices()
    {
        return $this->hasMany('Device');
    }

    /**
     * Relation Belongs To Many (User has many Organisations)
     */
    public function organisations()
    {
        return $this->belongsToMany('Organisation', 'organisation_user', 'user_id', 'organisation_id');
    }

    /**
     * Relation One To Many (User has many Skills)
     */
    public function skills()
    {
        return $this->hasMany('Skill')->orderBy('value', 'DESC');
    }

    /**
     * Relation One To Many (User has many Past Times)
     */
    public function pasttimes()
    {
        return $this->hasMany('PastTime');
    }

    public function booking_items()
    {
        return $this->belongsToMany('BookingItem');
    }

    /**
     * Fullname user
     */
    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Fullname user with Organisations
     */
    public function getFullnameOrgaAttribute()
    {
        $organisation = '';
        foreach ($this->organisations as $key => $orga) {
            if ($key > 0) {
                $organisation .= ', ';
            }
            $organisation .= $orga->name;
        }
        $result = $this->firstname . ' ' . $this->lastname;
        if (!empty($organisation) && ($result != $organisation)) {
            $result .= ' (' . $organisation . ')';
        }
        return $result;
    }

    /**
     * Fullname user with Organisations
     */
    public function getOrgaFullnameAttribute()
    {
        $organisation = '';
        foreach ($this->organisations as $key => $orga) {
            if ($key > 0) {
                $organisation .= ', ';
            }
            $organisation .= $orga->name;
        }
        $result = $this->firstname . ' ' . $this->lastname;
        if (!empty($organisation) && ($result != $organisation)) {
            $result = $organisation . ' (' . $result . ')';
        }
        return $result;
    }

    /**
     * List of skills
     */
    public function getAllSkillsAttribute()
    {
        $skills = array(
            'major' => array(),
            'minor' => ''
        );
        foreach ($this->skills as $skill) {
            if ($skill->value) {
                $skills['major'][] = array(
                    'name' => $skill->name,
                    'value' => $skill->value
                );
            } else {
                if ($skills['minor'] != '') {
                    $skills['minor'] .= ', ';
                }
                $skills['minor'] .= $skill->name;
            }
        }
        return $skills;
    }

    public function getAvatarTagAttribute()
    {
        return sprintf('<img alt="%s" class="img-circle m-t-xs" src="%s" title="%s">',
            $this->fullname,
            $this->avatarUrl,
            $this->fullnameOrga
        );


    }

    public function getAvatarTag38Attribute()
    {
        return sprintf('<img alt="%s" class="img-circle m-t-xs" src="%s" title="%s">',
            $this->fullname,
            $this->getAvatarUrl(38),
            $this->fullnameOrga
        );


    }

    public static function formatPhoneNumber($value)
    {
        $result = preg_replace('/[^0-9]/', '', $value);
        $result = preg_replace('/([0-9]{2})/', '\1 ', $result);
        return trim($result);
    }

    public function getPhoneFmtAttribute()
    {
        return self::formatPhoneNumber($this->phone);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->getAvatarUrl(80);
    }

    public function getAvatarUrl($size)
    {
        if (!empty($this->avatar)) {
            $src_filename = sprintf('/uploads/users/%d/%s', $this->id, $this->avatar);
            if (is_file(public_path() . $src_filename)) {
                $result = Croppa::url($src_filename, $size, $size, array('resize'));
                //$result = preg_replace('!^(.+)\?.+$!', '$1', $result);
                return $result;
            }
        }
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?d=mm&s=" . $size;
    }

    public function getLargeAvatarUrlAttribute()
    {
        return $this->getAvatarUrl(500);
    }

    /**
     * Get list of users
     */
    public function scopeSelect($query, $title = "Select", $selector = 'fullnameOrga')
    {
        $selectVals = array();

        if ($title) {
            $selectVals[''] = $title;
        }
        $selectVals += $query->orderBy('lastname', 'ASC')->with('organisations')->orderBy('firstname', 'ASC')->get()->lists($selector, 'id');
        return $selectVals;
    }

    /**
     * Get list of users not in an organisation selected
     */
    public function scopeSelectNotInOrganisation($query, $organisation, $title = "Select")
    {
        $ids = OrganisationUser::where('organisation_id', $organisation)->lists('user_id');
        $selectVals = array();
        if ($title) {
            $selectVals[''] = $title;
        }
        if ($ids) {
            $selectVals += $this->whereNotIn('id', $ids)->get()->lists('fullname', 'id');
        } else {
            $selectVals += $this->orderBy('lastname', 'ASC')->orderBy('firstname', 'ASC')->get()->lists('fullname', 'id');
        }
        return $selectVals;
    }

    /**
     * Get list of users in organisations
     */
    public function scopeSelectInOrganisation($query, $organisation, $title = "Select")
    {
        $ids = OrganisationUser::where('organisation_id', $organisation)->lists('user_id');
        $selectVals = array();
        if ($title) {
            $selectVals[''] = $title;
        }
        if ($ids) {
            $selectVals += $this->whereIn('id', $ids)
                ->orderBy('firstname', 'asc')
                ->orderBy('lastname', 'asc')
                ->get()->lists('fullname', 'id');
        }
        return $selectVals;
    }

    /**
     * Get list of roles
     */
    public function scopeSelectRoles()
    {
        $selectVals = array(
            'member' => 'Membre',
            'superadmin' => 'SuperAdmin'
        );
        return $selectVals;
    }

    static public function getRoles($name)
    {
        $roles = array(
            '' => array('member'),
            'member' => array('member'),
            'shareholder' => array('member', 'shareholder'),
            'superadmin' => array('member', 'shareholder', 'superadmin')
        );

        return in_array($name, $roles[Auth::user()->role]);
    }

    public function addSkill($skill)
    {

    }

    public function hasQuotes()
    {
        return (bool)(Invoice::whereType('D')->whereUserId($this->id)->count() > 0);
    }

    public function getPendingInvoiceCount()
    {
        return Invoice::whereType('F')
            ->select('invoices.id')
            ->join('organisations', 'organisations.id', '=', 'invoices.organisation_id')
            ->where(function ($query) {
                $query->where('organisations.accountant_id', $this->id)
                    ->orWhere('invoices.user_id', $this->id);
            })
            //->whereUserId($this->id)
            ->whereNull('date_payment')
            ->count();
    }

    public function isSuperAdmin()
    {
        return $this->getRoles('superadmin');
    }

    public function isShareholder()
    {
        return $this->getRoles('shareholder');
    }

    public function getLastSubscription()
    {
        return InvoiceItem::where('subscription_from', '<>', '0000-00-00 00:00:00')
            ->where('subscription_user_id', $this->id)
            ->orderBy('subscription_to', 'DESC')
            ->select('subscription_from', 'subscription_to', 'subscription_hours_quota', 'invoice_id')
            ->first();
    }

    public function getCoworkingTimeSpent($from, $to)
    {
        return (int)PastTime::where('user_id', $this->id)
            ->whereBetween('date_past', array($from, $to))
            ->where('ressource_id', Ressource::TYPE_COWORKING)
            ->select(DB::raw('sum((UNIX_TIMESTAMP(past_times.time_end) - UNIX_TIMESTAMP(past_times.time_start)) / 60) as amount'))
            ->first()->amount;
    }

    public function getActiveTimesheet()
    {
        return PastTime::where('user_id', $this->id)
            ->where('date_past', date('Y-m-d'))
            ->where('ressource_id', Ressource::TYPE_COWORKING)
            ->whereRaw('time_start < NOW()')
            ->where(function ($query) {
                $query->whereNull('time_end')
                    ->orWhereRaw('time_end > NOW()');
            })
            ->first();
    }

    public function location()
    {
        return $this->belongsTo('Location', 'default_location_id');
    }

    public static function getGenders()
    {
        return array(
            'M' => 'Homme',
            'F' => 'Femme',
            null => 'Inconnu'
        );
    }

    public function getWeeksAgoCss()
    {
        if (null == $this->last_seen_at) {
            return 'opacity10';
        }
        $days = time() - strtotime($this->last_seen_at);
        $days /= 24 * 60 * 60;
        $weeks = round($days / 7);
        switch ($weeks) {
            case 0:
                return 'opacity100';
            case 1:
                return 'opacity90';
            case 2:
                return 'opacity80';
            case 3:
                return 'opacity70';
            case 4:
                return 'opacity60';
            case 5:
                return 'opacity50';
            case 6:
                return 'opacity40';
            case 7:
                return 'opacity30';
            case 8:
                return 'opacity20';
            case 9:
                return 'opacity10';
            default:
                return 'opacity10';
        }
    }

    public function populateFromEmail($email)
    {
        $this->email = strtolower($email);
        $tokens = explode('@', $email);
        $items = preg_split('/[._-]/', $tokens[0]);
        switch (count($items)) {
            case 2:
                if (empty($this->firstname)) {
                    $this->firstname = ucfirst($items[0]);
                }
                if (empty($this->lastname)) {
                    $this->lastname = ucfirst($items[1]);
                }
                break;
            case 1:
                if (empty($this->lastname)) {
                    $this->lastname = ucfirst($items[0]);
                }
                break;
            default:
                if (empty($this->lastname)) {
                    $this->lastname = ucfirst($tokens[0]);
                }
        }
    }

    public static function SplitNameEmail($data)
    {
        if (preg_match('/^((.+)\s+)?<?([a-zA-Z0-9_.+-]+@([a-zA-Z0-9-]+.)+[a-zA-Z]+)>?$/', trim($data), $tokens)) {
            $result = self::SplitName($tokens[2]);
            $result['email'] = strtolower($tokens[3]);
            return $result;
        }
        return false;
    }

    public static function SplitName($name)
    {
        $data = preg_split('/\s/', trim($name));
        return array('firstname' => array_shift($data), 'lastname' => implode(' ', $data));
    }

    public function getTotalPhoneboxUsageOverLastPeriod()
    {
        $now = new \DateTime();
        $period_start = clone $now;
        $period_start->sub(new \DateInterval(sprintf('PT%dM', Phonebox::QUOTA_PERIOD)));

        $items = DB::select(DB::raw(sprintf('SELECT sum(time_to_sec(timediff(LEAST("%1$s", ended_at), started_at)) / 60) as used
            FROM phonebox_session WHERE user_id = %2$d AND ended_at > "%3$s"',
            $now->format('Y-m-d H:i:s'), $this->id, $period_start->format('Y-m-d H:i:s'))));

        $item = array_shift($items);
        return round($item->used);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_staff', true);
    }

    public function getUserGift($kind){
        return UserGift::join('gift_kind', 'gift_kind.id', '=', 'user_gift.kind_id')
            ->where('gift_kind.code', '=', $kind)
            ->where('user_id', '=', $this->id)
            ->select('user_gift.*')
            ->first();
    }
}
