<?php

class BankOperation
{
    protected $id;
    protected $occurs_at;
    protected $name;
    protected $amount;
    protected $comment;

    public function getOccursAt()
    {
        return $this->occurs_at;
    }

    public function setOccursAt($value)
    {
        $this->occurs_at = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($value)
    {
        $this->amount = $value;
    }

    public function setComment($value)
    {
        $this->comment = $value;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function __construct($occurs_at, $name, $amount)
    {
        $this->setOccursAt($occurs_at);
        $this->setName($name);
        $this->setAmount($amount);
    }
}
