<?php

use Stripe\Stripe;

class CashflowAccount extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cashflow_account';

    /**
     * Rules
     */
    public static $rules = array();

    protected $guarded = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

    public static function build($name, $amount = 0)
    {
        $account = new CashflowAccount();
        $account->name = $name;
        $account->amount = $amount;
        $account->save();
        return $account;
    }

    public function createOperation($occurs_at, $amount, $name, $frequency = null)
    {
        $operation = new CashflowOperation();
        $operation->account_id = $this->id;
        $operation->occurs_at = new \DateTime($occurs_at);
        $operation->amount = $amount;
        $operation->name = $name;
        if ($frequency) {
            $operation->frequency = $frequency;
        }
        $operation->save();
        return $operation;
    }

    public function getDailyOperations($duration = '3 months')
    {
        $ends_at = (new \DateTime())->modify($duration)->format('Y-m-d');
        $result = array();
        $start_at = date('Y-m-d');
        while ($start_at <= $ends_at) {
            $result[$start_at]['operations'] = array();
            $result[$start_at]['amount'] = 0;
            $start_at = (new \DateTime($start_at))->modify('+1 day')->format('Y-m-d');
        }

        if ($_ENV['stripe_sk']) {
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
                $result[$date]['operations'][] = array(
                    'id' => null,
                    'name' => sprintf('Stripe %s', date('d/m/Y', strtotime($date))),
                    'amount' => $amount,
                    'refreshable' => false
                );
            }
        }

        $operations = CashflowOperation::where('account_id', $this->id)
            ->where('archived', false)
            ->where('occurs_at', '<', $ends_at)
            ->get();

        foreach ($operations as $operation) {
            if ($operation->frequency) {
                $start_at = $operation->occurs_at;
                while ($start_at < $ends_at) {
                    $result[$start_at]['operations'][] = array(
                        'id' => $operation->id,
                        'name' => CashflowOperation::formatName($operation->name, $start_at),
                        'amount' => $operation->amount,
                        'refreshable' => true
                    );
                    $start_at = (new \DateTime($start_at))
                        ->modify(sprintf('+%s', $operation->frequency))
                        ->format('Y-m-d');
                }
            } else {
                $start_at = $operation->occurs_at;
                $result[$start_at]['operations'][] = array(
                    'id' => $operation->id,
                    'name' => CashflowOperation::formatName($operation->name, $start_at),
                    'amount' => $operation->amount,
                    'refreshable' => false
                );
            }
        }
        // print_r($result);
        // exit;
        $amount = $this->amount;
        foreach ($result as $date => $data) {
            foreach ($data['operations'] as $operation) {
                $amount += $operation['amount'];
            }
            $result[$date]['amount'] = $amount;
        }

        return $result;
    }

}
