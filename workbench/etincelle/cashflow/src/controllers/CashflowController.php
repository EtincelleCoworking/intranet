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
        foreach($params['accounts'] as $account) {
            foreach($account->getDailyOperations() as $date => $data){
                $params['charts'][$account->id][] = array(
                    'date' => $date,
                    'value' => $data['amount']);
            }
        }

        return View::make('cashflow::index', $params);
    }

    public function graph()
    {
        $charts = array();

        foreach (CashflowAccount::all() as $account) {
            foreach($account->getDailyOperations() as $date => $data){

            $charts[$account->name][$date] = $data['amount'];
            }
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
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