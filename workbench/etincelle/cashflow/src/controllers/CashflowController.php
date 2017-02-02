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

        return View::make('cashflow::index', $params);
    }

    public function delete($id)
    {
        $operation = CashflowOperation::findOrFail($id);
        $operation->archived = true;
        $operation->save();
        return Redirect::route('cashflow', 'all')->with('mSuccess', sprintf('L\'opération %s a été supprimée', $operation->name));
    }

    public function refresh($id)
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