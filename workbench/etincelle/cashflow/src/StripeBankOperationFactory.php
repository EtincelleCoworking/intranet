<?php

use Stripe\Stripe;

class StripeBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        if (!empty($_ENV['stripe_sk'])) {
            $cacheKey = 'stripe.upcoming_transfers';
            if (Cache::has($cacheKey)) {
                $stripe_items = Cache::get($cacheKey);
            } else {
                Stripe::setApiKey($_ENV['stripe_sk']);

                $items = \Stripe\Transfer::all(array('status' => 'pending'));
                $stripe_items = array();
                foreach ($items->data as $item) {
                    $stripe_items[date('Y-m-d', $item->date)] = $item->amount / 100;
                }
                Cache::put($cacheKey, $stripe_items, 15);
            }

            foreach ($stripe_items as $date => $amount) {
                $collection->register(new BankOperation($date, sprintf('Stripe %s', date('d/m/Y', strtotime($date))), $amount));
            }
        }
    }
}