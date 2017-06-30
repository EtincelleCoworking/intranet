<?php

class IdentifiedBankOperation extends BankOperation
    implements IDeletableBankOperation, IEditableBankOperation, IActionsProviderBankOperation
{
    protected $id;

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId()
    {
        return $this->id;
    }

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

    public function __construct($occurs_at, $name, $amount, $id)
    {
        parent::__construct($occurs_at, $name, $amount);
        $this->setId($id);
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
