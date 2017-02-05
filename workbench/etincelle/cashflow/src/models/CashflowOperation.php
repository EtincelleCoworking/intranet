<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CashflowOperation extends Illuminate\Database\Eloquent\Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cashflow_operation';

    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

    protected $guarded = array();

    public static function formatName($name, $occurs_at)
    {
        $macros = array();
        $ts = strtotime($occurs_at);
        $macros['%today%'] = (new \DateTime($occurs_at))->format('d/m/Y');
        $macros['%week%'] = (new \DateTime($occurs_at))->format('\s\e\m. W/Y');
        $macros['%month.last%'] = (new \DateTime($occurs_at))->modify('-1 month')->format('m/Y');
        $macros['%month%'] = (new \DateTime($occurs_at))->format('m/Y');
        $macros['%quarter%'] = 'Q' . ceil(date('n', $ts) / 3) . '/' . date('Y', $ts);

        $lastQuarter = (new \DateTime($occurs_at))->modify('-3 months');
        $macros['%quarter.last%'] = 'Q' . ceil($lastQuarter->format('n') / 3) . '/' . $lastQuarter->format('Y');
        $macros['%year%'] = (new \DateTime($occurs_at))->format('Y');

        return str_replace(array_keys($macros), array_values($macros), $name);
    }

    public static function getAvailableFrequencies()
    {
        $result = array();
        $result[''] = 'Aucune';
        $result['+1 day'] = 'Quotidienne';
        $result['+1 week'] = 'Hebdomadaire';
        $result['+1 month'] = 'Mensuelle';
        $result['+3 month'] = 'Trimestrielle';
        $result['+1 year'] = 'Annuelle';
        return $result;
    }

    public function buildBankOperation($occurs_at = null)
    {
        if ($this->frequency) {
            $when = $occurs_at ? $occurs_at : $this->occurs_at;
            $result = new RecurringIdentifiedBankOperation($when, $this->name, $this->amount, $this->frequency, $this->id);
            $result->registerAction(new BankOperationAction\Archive(URL::route('cashflow_operation_refresh', array('account_id' => $this->account_id, 'id' => $this->id))));
        } else {
            $when = $occurs_at ? $occurs_at : $this->occurs_at;
            $result = new ManagedBankOperation($when, CashflowOperation::formatName($this->name, $when), $this->amount);
            $result->registerAction(new BankOperationAction\Archive(URL::route('cashflow_operation_archive', array('account_id' => $this->account_id, 'id' => $this->id))));
        }
        $result->setDeleteLink(URL::route('cashflow_operation_delete', array('account_id' => $this->account_id, 'id' => $this->id)));
        $result->setEditLink(URL::route('cashflow_operation_modify', array('account_id' => $this->account_id, 'id' => $this->id)));
        return $result;
    }

}
