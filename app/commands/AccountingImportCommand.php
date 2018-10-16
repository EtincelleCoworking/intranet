<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
//SELECT date_format( occurs_at, '%Y-%m') as m, count(*) as operation_count FROM `accounting_bank_operations` group by m order by m ASC

class AccountingImportCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'accounting:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->output->writeln('');
        $accountNo = $this->option('account');

        $filename = $this->argument('filename');
        $result = $this->parseOfxFile($filename);

        $accountNos = array_keys($result);
        if (count($result) == 1 && empty($accountNo)) {
            $accountNo = $accountNos[0];
        }
        if (!isset($result[$accountNo])) {
            $this->output->writeln(sprintf('<error>Missing or unknown account (%s).</error>', $filename));
            $this->output->writeln('Available accounts: ');
            foreach ($accountNos as $accountNo) {
                $this->output->writeln(sprintf('- %s', $accountNo));
            }
            return false;
        }

        $this->output->writeln(sprintf('Importing account %s from %s', $accountNo, $filename));

        $count = 0;
        foreach ($result[$accountNo]['operations'] as $operation) {
            $o = $this->importOperation($operation);
            if ($o) {
                $count++;
                $this->output->writeln(sprintf('%10s %15s     %s', $o->occurs_at, $o->amount, $o->name));
                $this->output->writeln(sprintf('%10s %15s     %s', '', '', $o->memo));
                $this->output->writeln(sprintf('%10s %15s     %s', '', '', $o->check_no));
                $this->output->writeln(sprintf('--------------------------------------------------------------'));
            } else {
                //$this->output->writeln('(duplicate)');
                $this->output->write('.');
            }
        }

        $import = new \Accounting\BankOperationImport();
        $import->filename = basename($filename);
        $import->balance_at = $result[$accountNo]['balanceDate'];
        $import->balance_amount = $result[$accountNo]['balance'];
        $import->imported_count = $count;
        $import->save();
        //print_r($result);
    }

    protected function computeKey($operation)
    {
        return $operation['id'];
    }

    protected function importOperation($operation)
    {
        static $cache = null;
        if (null == $cache) {
            $cache = \Accounting\BankOperation::lists('reference');
        }
        $key = $this->computeKey($operation);
        if (in_array($key, $cache)) {
            return false;
        }
        $result = new \Accounting\BankOperation();
        $result->reference = $key;
        $result->occurs_at = $operation['occurs_at'];
        $result->name = $operation['name'];
        $result->memo = $operation['memo'];
        $result->amount = $operation['amount'];
        $result->check_no = $operation['checkNumber'];
        $result->save();
        return $result;
    }

    protected function parseOfxFile($filename)
    {
        $ofxParser = new \OfxParser\Parser();
        $ofx = $ofxParser->loadFromFile($filename);
        $result = array();
        foreach ($ofx->bankAccounts as $bankAccount) {
            $operations = array();
            foreach ($bankAccount->statement->transactions as $transaction) {
                $operations[] = array(
                    'id' => $transaction->uniqueId,
                    'occurs_at' => $transaction->date->format('Y-m-d'),
                    'name' => (string)$transaction->name,
                    'memo' => (string)$transaction->memo,
                    'amount' => (float)$transaction->amount,
                    'checkNumber' => (string)$transaction->checkNumber,
                );
            }
            $result[(string)$bankAccount->accountNumber] = array(
                'balance' => (float)$bankAccount->balance,
                'balanceDate' => $bankAccount->balanceDate->format('Y-m-d'),
                'operations' => $operations
            );
        }
        return $result;
    }

    protected function getArguments()
    {
        return array(
            array('filename', InputArgument::REQUIRED, 'File to import'),
        );
    }

    protected function getOptions()
    {
        return array(
            array('account', null, InputOption::VALUE_OPTIONAL, 'Account No to import', null),
        );
    }

}
