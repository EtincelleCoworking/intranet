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
        (new SubscriptionBankOperationFactory())->populate($collection);
        (new CashflowBankOperationFactory($this->id))->populate($collection);
        (new InvoiceBankOperationFactory($this->id))->populate($collection);
        (new VatBankOperationFactory($this->id))->populate($collection);

        return $collection->getItems($this->amount);
    }

    public function processOperations($operations)
    {
        $result = array();
        foreach ($operations as $operation) {
            $result[] = $this->processOperation($operation);
        }
        return $result;
    }

    protected function checkInvoicePayment($operation, &$message, &$status)
    {
        if (preg_match('/(F[ ]?([0-9]{6})[- ]([0-9]{4}))/', $operation['comment'], $tokens)
            || preg_match('/[^0-9](20([0-9]{4})[- ]?([0-9]{4}))([^0-9]|$)/', $operation['comment'], $tokens)
        ) {
            $invoice = Invoice::where('type', 'F')
                ->where('days', $tokens[2])
                ->where('number', $tokens[3])
                ->first();
            if ($invoice) {
                $invoice_amount = Invoice::TotalInvoiceWithTaxes($invoice->items);
                if ($invoice_amount != $operation['amount']) {
                    $message = sprintf('<p>Invoice %s has a different amount (operation = %s€, invoice = %s€)</p>', (string)$invoice, $operation['amount'], $invoice_amount);
                    $status = 'warning';
                    return true;
                } else {
                    if ($invoice->date_payment) {
                        $message = sprintf('<p>Invoice %s has already been paid</p>', (string)$invoice);
                        $status = 'info';
                        return true;
                    } else {
                        $invoice->date_payment = $operation['occurs_at'];
                        $invoice->save();
                        $status = 'success';
                        $message = sprintf('<p>Invoice %s has been marked as paid</p>', (string)$invoice);
                        return true;
                    }
                }
            } else {
                //printf('<p>Unable to find invoice %s</p>', $tokens[1]);
            }
        }
        return false;
    }

    protected function checkExistingOperation($operation, &$message)
    {
        $begin_at = (new \DateTime($operation['occurs_at']))->modify('- 3 days')->format('Y-m-d');
        $end_at = (new \DateTime($operation['occurs_at']))->modify('+ 3 days')->format('Y-m-d');
        $existing_operation = CashflowOperation::where('line1', $operation['name'])
            ->where('line2', $operation['comment'])
            ->where('amount', $operation['amount'])
            ->where('occurs_at', '>=', $begin_at)
            ->where('occurs_at', '<=', $end_at)
            ->first();
        if (!$existing_operation) {
            // $message = sprintf('<p>Unable to find operation [%s] %s€ near %s</p>', $operation['name'], $operation['amount'], $operation['occurs_at']);
            return false;
        }
        if ($existing_operation->frequency) {
            $existing_operation->occurs_at = (new \DateTime($existing_operation->occurs_at))->modify(sprintf('+%s', $existing_operation->frequency))->format('Y-m-d');;
            $existing_operation->save();
            $message = sprintf('<p>Recurring operation [%s] %s€ on %s has been refreshed</p>', (string)$existing_operation, $operation['amount'], $operation['occurs_at']);
            return true;
        }
        $existing_operation->archived = true;
        $existing_operation->save();
        $message = sprintf('<p>Operation [%s] %s€ on %s has been archived</p>', (string)$existing_operation, $operation['amount'], $operation['occurs_at']);
        return true;
    }

    protected function checkStripeOperation($operation)
    {
        $stripe_operations = StripeBankOperationFactory::getUpcomingStripeOperations('in_transit');
        foreach ($stripe_operations as $occurs_at => $amount) {
            if ($operation['name'] == 'VIR Stripe Payments UK L'
                && $operation['amount'] == $amount
                //    && $operation['occurs_at'] == $occurs_at
            ) {
                return true;
            }
        }
    }

    public function processCharges($operations)
    {
        foreach ($operations as $operation) {
            if ($operation['amount'] < 0) {
                $this->addCharge($operation);
            }
        }
    }

    public function processOperation($operation)
    {
        $result = array(
            'occurs_at' => $operation['occurs_at'],
            'text' => implode('<br />', array($operation['name'], $operation['comment'])),
            'amount' => $operation['amount'],
            'status' => 'danger',
            'module' => false,
            'comment' => false,
        );
        if ($this->checkInvoicePayment($operation, $message, $status)) {
            $result['status'] = $status;
            $result['module'] = 'invoice';
            $result['comment'] = $message;
            return $result;
        }
        if ($this->checkExistingOperation($operation, $message)) {
            $result['status'] = 'success';
            $result['module'] = 'operation';
            $result['comment'] = $message;
            return $result;
        }
        if ($this->checkStripeOperation($operation)) {
            $result['status'] = 'success';
            $result['module'] = 'stripe';
            return $result;
        }
        return $result;
    }

    public function addCharge($operation)
    {
        $item = ChargeItem::join('charges', 'charges.id', '=', 'charges_items.charge_id')
            ->where('description', '=', implode(' ', array($operation['name'], $operation['comment'])))
            //->where('amount', '=', -1 * $operation['amount'])
            ->where('charges.date_charge', '=', $operation['occurs_at'])
            ->with('charge')
            ->first();
        if (!$item) {
            $charge = new Charge();
            $charge->date_charge = $operation['occurs_at'];
            $charge->date_payment = $operation['occurs_at'];
            $charge->save();

            $item = new ChargeItem();
            $item->charge_id = $charge->id;
            $item->description = implode(' ', array($operation['name'], $operation['comment']));
            $item->amount = -1 * $operation['amount'] / 1.2;
            $item->vat_types_id = 1;
            $item->save();
        }
    }

}
