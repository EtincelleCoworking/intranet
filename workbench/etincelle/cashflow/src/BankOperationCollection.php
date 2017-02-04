<?php

class BankOperationCollection
{
    protected $items = array();
    protected $today = null;
    protected $ends_at = null;

    public function __construct($ends_at)
    {
        $this->today = date('Y-m-d');
        $this->ends_at = $ends_at;
        $start_at = $this->today;
        while ($start_at <= $ends_at) {
            $this->items[$start_at]['operations'] = array();
            $this->items[$start_at]['amount'] = 0;
            $start_at = (new \DateTime($start_at))->modify('+1 day')->format('Y-m-d');
        }
    }

    public function register(BankOperation $operation)
    {
        $occurs_at = ($this->today > $operation->getOccursAt()) ? $this->today : $operation->getOccursAt();
        $this->items[$occurs_at]['operations'][] = $operation;
    }

    public function getItems($initial_amount)
    {
        $result = $this->items;
        $amount = $initial_amount;
        foreach ($result as $date => $data) {
            foreach ($data['operations'] as $operation) {
                $amount += $operation->getAmount();
            }
            $result[$date]['amount'] = $amount;
        }

        return $result;
    }

    public function getEndsAt(){
        return $this->ends_at;
    }
}
