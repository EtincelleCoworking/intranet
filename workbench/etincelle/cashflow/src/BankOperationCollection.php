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
            $this->items[$start_at]['positive'] = 0;
            $this->items[$start_at]['negative'] = 0;
            $start_at = (new \DateTime($start_at))->modify('+1 day')->format('Y-m-d');
        }
        ksort($this->items);
    }

    public function register(BankOperation $operation)
    {
        if ($operation->getOccursAt() <= $this->ends_at) {
            $occurs_at = ($this->today > $operation->getOccursAt()) ? $this->today : $operation->getOccursAt();
            if ($occurs_at != $operation->getOccursAt()) {
                $operation->setCOmment(sprintf('Date: %s', $operation->getOccursAt()));
            }
            $this->items[$occurs_at]['operations'][] = $operation;
        }
    }

    public function getItems($initial_amount)
    {
        $result = $this->items;

        $amount = $initial_amount;
        foreach ($result as $date => $data) {
            $positive = 0;
            $negative = 0;
            foreach ($data['operations'] as $operation) {
                if ($operation->getAmount() > 0) {
                    $positive += $operation->getAmount();
                } else {
                    $negative += $operation->getAmount();
                }
            }
            $amount += $positive;
            $amount += $negative;
            $result[$date]['positive'] = $positive;
            $result[$date]['negative'] = $negative;
            $result[$date]['amount'] = $amount;
        }

        return $result;
    }

    public function getEndsAt()
    {
        return $this->ends_at;
    }
}
