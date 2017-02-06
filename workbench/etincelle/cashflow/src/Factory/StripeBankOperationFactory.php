<?php

use Stripe\Stripe;

class StripeBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        foreach (self::getUpcomingStripeOperations() as $date => $amount) {
            $collection->register(new BankOperation($date, sprintf('Stripe %s', date('d/m/Y', strtotime($date))), $amount));
        }

    }

    static public function getUpcomingStripeOperations($status = 'pending')
    {
        $stripe_items = array();
        if (!empty($_ENV['stripe_sk'])) {
            $cacheKey = 'stripe.upcoming_transfers.'.$status;
            if (Cache::has($cacheKey)) {
                $stripe_items = Cache::get($cacheKey);
            } else {
                Stripe::setApiKey($_ENV['stripe_sk']);

                $items = \Stripe\Transfer::all(array('status' => $status));
                foreach ($items->data as $item) {
                    $stripe_items[date('Y-m-d', $item->date)] = $item->amount / 100;
                }
                Cache::put($cacheKey, $stripe_items, 15);
            }
        }
        return $stripe_items;
    }
}