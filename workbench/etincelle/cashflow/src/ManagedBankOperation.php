<?php

class ManagedBankOperation extends BankOperation
    implements IDeletableBankOperation, IEditableBankOperation, IActionsProviderBankOperation
{

    protected $delete_link;

    public function setDeleteLink($value)
    {
        $this->delete_link = $value;
    }

    public function getDeleteLink()
    {
        return $this->delete_link;
    }

    protected $edit_link;

    public function setEditLink($value)
    {
        $this->edit_link = $value;
    }

    public function getEditLink()
    {
        return $this->edit_link;
    }

    protected $actions = array();

    public function getBankOperationActions()
    {
        return $this->actions;
    }

    public function registerAction($action){
        $this->actions[] = $action;
    }
}
