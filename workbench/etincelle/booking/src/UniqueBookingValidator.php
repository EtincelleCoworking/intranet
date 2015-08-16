<?php

/**
 * Created by PhpStorm.
 * User: shordeaux
 * Date: 14/08/2015
 * Time: 11:35
 */
class UniqueBookingValidator extends Illuminate\Validation\Validator
{
    public function validate($attribute, $value, $parameters){
var_dump($attribute);
var_dump($value);
var_dump($parameters);
        exit;
    }
}

Validator::extend('unique_booking', 'UniqueBookingValidator@validate');