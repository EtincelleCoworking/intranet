<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Skill extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'skills';

    /**
     * The guarded fields
     */
    protected $guarded = array('id');

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required',
        'name' => 'required'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required',
        'name' => 'required'
    );

    /**
     * Relation One To One (Skill has One User)
     */
    public function skill()
    {
        return $this->hasOne('User');
    }

    static public function findSkillsForUser($userId)
    {
        $skills = DB::table('skills')->where('user_id', $userId)->get();
        return $skills;
    }
}
