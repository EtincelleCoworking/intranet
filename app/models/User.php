<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{

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

    public function getPhoneFmtAttribute()
    {
        $result = preg_replace('/[^0-9]/', '', $this->phone);
        $result = preg_replace('/([0-9]{2})/', '\1 ', $result);
        return $result;
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
        return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?d=mm&s=" . $size;
    }

    public function getLargeAvatarUrlAttribute()
    {
        return $this->getAvatarUrl(500);
    }

    /**
     * Get list of users
     */
    public function scopeSelect($query, $title = "Select")
    {
        $selectVals[''] = $title;
        $selectVals += $this->orderBy('lastname', 'ASC')->with('organisations')->orderBy('firstname', 'ASC')->get()->lists('fullnameOrga', 'id');
        return $selectVals;
    }

    /**
     * Get list of users not in an organisation selected
     */
    public function scopeSelectNotInOrganisation($query, $organisation, $title = "Select")
    {
        $ids = OrganisationUser::where('organisation_id', $organisation)->lists('user_id');
        $selectVals[''] = $title;
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
        $selectVals[''] = $title;
        if ($ids) {
            $selectVals += $this->whereIn('id', $ids)->get()->lists('fullname', 'id');
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
            'superadmin' => array('member', 'superadmin')
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
        return Invoice::whereType('F')->whereUserId($this->id)->whereNull('date_payment')->count();
    }

    public function isSuperAdmin()
    {
        return ($this->role == 'superadmin');
    }

    public function getLastSubscription()
    {
        return InvoiceItem::join('invoices', 'invoice_id', '=', 'invoices.id')
            ->where('subscription_from', '<>', '0000-00-00 00:00:00')
            ->where('invoices.user_id', $this->id)
            ->orderBy('subscription_to', 'DESC')
            ->select('subscription_from', 'subscription_to', 'subscription_hours_quota', 'invoice_id')
            ->first();
    }

    public function getCoworkingTimeSpent($from, $to)
    {
        return PastTime::where('user_id', $this->id)
            ->whereBetween('date_past', array($from, $to))
            ->where('ressource_id', Ressource::TYPE_COWORKING)
            ->select(DB::raw('sum((TIME_TO_SEC(past_times.time_end) - TIME_TO_SEC(past_times.time_start)) / 60) as amount'))
            ->first()->amount;
    }
    public function getActiveTimesheet(){
        return PastTime::where('user_id', $this->id)
            ->where('date_past', date('Y-m-d'))
            ->where('ressource_id', Ressource::TYPE_COWORKING)
            ->whereRaw('time_start < NOW()')
            ->where(function($query)
            {
                $query->whereNull('time_end')
                    ->orWhereRaw('time_end > NOW()');
            })
            ->first();
    }

    public function location()
    {
        return $this->belongsTo('Location', 'default_location_id');
    }
}
