<?php

class RessourceKind extends Eloquent
{
    protected $table = 'ressource_kind';

    const TYPE_COWORKING = 1;
    const TYPE_EXCEPTIONNAL = 4;

    public function __toString(){
        return $this->name;
    }

}