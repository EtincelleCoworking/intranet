<?php

class CashflowOperation extends Illuminate\Database\Eloquent\Model
{
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

    public function formatName($occurs_at)
    {
        $macros = array();
        $macros['%today%'] = (new \DateTime($occurs_at))->format('d/m/Y');
        $macros['%week%'] = (new \DateTime($occurs_at))->format('\s\e\m. W/Y');
        $macros['%month.last%'] = (new \DateTime($occurs_at))->modify('-1 month')->format('m/Y');
        $macros['%month%'] = (new \DateTime($occurs_at))->format('m/Y');
        $macros['%year%'] = (new \DateTime($occurs_at))->format('Y');

        return str_replace(array_keys($macros), array_values($macros), $this->name);
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

}
