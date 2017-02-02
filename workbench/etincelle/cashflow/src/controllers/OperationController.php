<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class OperationController extends Controller
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = CashflowOperation::find($id);
        if (!$data) {
            return Redirect::route('cashflow')->with('mError', 'Opération inconnue!');
        } else {
            return $data;
        }
    }
    /**
     * Modify operation
     */
    public function modify($id)
    {
        $operation = $this->dataExist($id);

        return View::make('cashflow::modify', array('operation' => $operation));
    }

    /**
     * Modify operation (form)
     */
    public function modify_check($id)
    {
        $operation = $this->dataExist($id);

        $validator = Validator::make(Input::all(), CashflowOperation::$rules);
        if (!$validator->fails()) {
            $operation->update(Input::all());
            $occurs_at = explode('/', Input::get('occurs_at'));
            $operation->occurs_at = $occurs_at[2] . '-' . $occurs_at[1] . '-' . $occurs_at[0];

            if ($operation->save()) {
                return Redirect::route('cashflow', $operation->id)->with('mSuccess', 'Cette opération a bien été modifié');
            } else {
                return Redirect::route('cashflow_modify', $operation->id)->with('mError', 'Impossible de modifier cette opération')->withInput();
            }
        } else {
            return Redirect::route('cashflow_modify', $operation->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add operation
     */
    public function add($account_id)
    {
        return View::make('cashflow::modify', array('account_id' => $account_id));
    }

    /**
     * Add operation check
     */
    public function add_check($account_id)
    {
        $validator = Validator::make(Input::all(), CashflowOperation::$rulesAdd);
        if (!$validator->fails()) {
            $operation = new CashflowOperation(Input::all());

            $occurs_at = explode('/', Input::get('occurs_at'));
            $operation->occurs_at = $occurs_at[2] . '-' . $occurs_at[1] . '-' . $occurs_at[0];


            if ($operation->save()) {
                return Redirect::route('cashflow', $operation->id)->with('mSuccess', 'L\'opération a bien été modifiée');
            } else {
                return Redirect::route('cashflow_add')->with('mError', 'Impossible de créer cette opération')->withInput();
            }
        } else {
            return Redirect::route('cashflow_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }
}