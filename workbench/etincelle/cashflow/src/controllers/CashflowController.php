<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Stripe\Stripe;

class CashflowController extends Controller
{
    public function index()
    {
        $params = array();

        $params['accounts'] = CashflowAccount::all();
        $params['charts'] = array();
        foreach ($params['accounts'] as $account) {
            foreach ($account->getDailyOperations() as $date => $data) {
                $params['charts'][$account->id][] = array(
                    'date' => $date,
                    'value' => $data['amount']);
            }
        }

        return View::make('cashflow::index', $params);
    }

    public function update()
    {
        $params = array();
        return View::make('cashflow::update', $params);
    }

    public function handle_update()
    {
        if (Input::hasFile('file')) {
            $fileObject = Input::file('file');
            if ($fileObject->isValid()) {
                $movedFile = $fileObject->move('../app/storage/ofx', sprintf('%s.%s', date('Y-m-d_His'), $fileObject->getClientOriginalExtension()));
                $result = array();
                $ofxParser = new \OfxParser\Parser();
                $ofx = $ofxParser->loadFromFile($movedFile->getPathName());
                $messages = array();
                foreach ($ofx->bankAccounts as $bankAccount) {
                    $operations = array();
                    foreach ($bankAccount->statement->transactions as $transaction) {
                        $operations[] = array(
                            'occurs_at' => $transaction->date->format('Y-m-d'),
                            'name' => (string)$transaction->name,
                            'comment' => (string)$transaction->memo,
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
                foreach (CashflowAccount::all() as $account) {
                    if (isset($result[$account->account_number])) {
                        if ($account->amount_updated_at < $result[$account->account_number]['balanceDate']) {
                            $report = $account->processOperations($result[$account->account_number]['operations']);

                            $account->amount = $result[$account->account_number]['balance'];
                            $account->amount_updated_at = $result[$account->account_number]['balanceDate'];
                            $account->save();
                            $message = sprintf('Le solde du compte %s a été mis à jour (%s€ en date du %s)',
                                $account->account_number, $account->amount, date('d/m/Y', strtotime($account->amount_updated_at)));

                            return View::make('cashflow::update_report', array('report' => $report, 'message' => $message));
                        } else {
                            $messages[] = sprintf('Le compte %s a déjà été mis à jour avec une version plus récente (mise à jour le %s, date du fichier: %s) - aucune opération effectuée',
                                $account->account_number,
                                date('d/m/Y', strtotime($account->amount_updated_at)),
                                date('d/m/Y', strtotime($result[$account->account_number]['balanceDate'])));
                        }
                    } else {
                        $messages[] = sprintf('Aucune information de mise à jour trouvée pour le compte %s', $account->account_number);
                    }
                }
                return Redirect::route('cashflow_update')->with('mInfo', implode('<br />', $messages));
            } else {
                return Redirect::route('cashflow_update')->with('mError', 'Fichier invalide');
            }
        } else {
            return Redirect::route('cashflow_update');
        }


    }

    public function delete($account_id, $id)
    {
        $operation = CashflowOperation::findOrFail($id);
        $message = sprintf('L\'opération %s a été supprimée', $operation->name);
        $operation->delete();
        return Redirect::route('cashflow', 'all')->with('mSuccess', $message);
    }


    public function archive($account_id, $id)
    {
        $operation = CashflowOperation::findOrFail($id);
        $operation->archived = true;
        $operation->save();
        return Redirect::route('cashflow', 'all')->with('mSuccess', sprintf('L\'opération %s a été archivée', $operation->name));
    }

    public function refresh($account_id, $id)
    {
        $operation = CashflowOperation::findOrFail($id);
        $start_at = $operation->occurs_at;
        $operation->occurs_at = (new \DateTime($operation->occurs_at))
            ->modify(sprintf('+%s', $operation->frequency))
            ->format('Y-m-d');
        $operation->save();
        return Redirect::route('cashflow', 'all')->with('mSuccess', sprintf('L\'opération %s a été modifiée (%s => %s)', $operation->name, $start_at, $operation->occurs_at));
    }
}