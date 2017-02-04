<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Stripe\Stripe;

class CashflowAccount extends Illuminate\Database\Eloquent\Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
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

    protected function createOperationArray($name, $amount, $id = false, $comment = false, $is_recurring = false)
    {
        return array(
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'comment' => $comment,
            'refreshable' => $is_recurring);
    }

    public function getDailyOperations($duration = '3 months')
    {
        $ends_at = (new \DateTime())->modify($duration)->format('Y-m-d');

        $collection = new BankOperationCollection($ends_at);
        (new StripeBankOperationFactory())->populate($collection);
//        (new SubscriptionBankOperationFactory())->populate($collection);
        (new CashflowBankOperationFactory($this->id))->populate($collection);
        (new InvoiceBankOperationFactory($this->id))->populate($collection);

        return $collection->getItems($this->amount);
    }

}
