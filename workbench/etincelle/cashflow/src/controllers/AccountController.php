<?php

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class AccountController extends Controller
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = CashflowAccount::find($id);
        if (!$data) {
            return Redirect::route('cashflow')->with('mError', 'Compte inconnu!');
        } else {
            return $data;
        }
    }

    /**
     * Modify account (form)
     */
    public function modify_check($id)
    {
        $account = $this->dataExist($id);

        $validator = Validator::make(Input::all(), CashflowAccount::$rules);
        if (!$validator->fails()) {
            $account->amount = Input::get('amount');

            if ($account->save()) {
                return Redirect::route('cashflow', $account->id)->with('mSuccess', 'Ce compte a bien été modifié');
            } else {
                return Redirect::route('cashflow', array('account_id' => $account->account_id, 'id' => $account->id))->with('mError', 'Impossible de modifier ce compte')->withInput();
            }
        } else {
            return Redirect::route('cashflow', array('account_id' => $account->account_id, 'id' => $account->id))->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

}