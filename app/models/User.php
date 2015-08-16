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
        'password' => 'required|min:5'
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
        if ($organisation) {
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
        return sprintf('<img alt="" class="img-circle m-t-xs" src="%s">', $this->avatarUrl);


    }

    public function getPhoneFmtAttribute()
    {
        $result = preg_replace('/[^0-9]/', '', $this->phone);
        $result = preg_replace('/([0-9]{2})/', '\1 ', $result);
        return $result;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->getGravatarUrl(80);
    }

    public function getGravatarUrl($size)
    {
        //return '/img/profile_small.jpg';
        $default = '404';
        $default = 'mm';
        //$default = 'identicon';
        //$default = 'monsterid';
        //$default = 'wavatar';
        return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?d=" . urlencode($default) . "&s=" . $size;
    }

    public function getLargeAvatarUrlAttribute()
    {
        return $this->getGravatarUrl(500);
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
}
