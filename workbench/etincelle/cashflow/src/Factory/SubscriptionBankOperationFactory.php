<?php

class SubscriptionBankOperationFactory extends AbstractBankOperationFactory
{
    public function populate(BankOperationCollection $collection)
    {
        $today = date('Y-m-d');
        foreach (Subscription::all() as $subscription) {
            /** @var Subscription $subscription */
            //var_dump((float)$subscription->kind->price);
            $start_at = $subscription->renew_at;
            while ($start_at < $collection->getEndsAt()) {
                $occurs_at = (new \DateTime($start_at))->modify('+1 month')->format('Y-m-d');
                $operation = new ManagedBankOperation($occurs_at, $subscription->formattedName(), (float)$subscription->kind->price * 1.2);
                if ($today > $start_at) {
                    $operation->setComment(sprintf('Date: %s', date('d/m/Y', strtotime($start_at))));
                }

                $operation->setDeleteLink(URL::route('subscription_delete', $subscription->id));
                $operation->setEditLink(URL::route('subscription_modify', $subscription->id));
                //$operation->registerAction(new BankOperationAction\Refresh(URL::route('subscription_renew', $subscription->id)));

                $collection->register($operation);
                $start_at = (new \DateTime($start_at))
                    ->modify(sprintf('+%s', $subscription->kind->duration))
                    ->format('Y-m-d');
            }
        }
    }
}