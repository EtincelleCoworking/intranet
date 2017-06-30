<?php

class RecurringIdentifiedBankOperation extends RecurringBankOperation
{
    protected $id;
    protected $delete_link;

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId()
    {
        return $this->id;
    }

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

    public function __construct($occurs_at, $name, $amount, $frequency, $id)
    {
        parent::__construct($occurs_at, $name, $amount, $frequency);
        $this->setId($id);
    }


    protected $actions = array();

    public function getBankOperationActions()
    {
        return $this->actions;
    }

    public function registerAction($action)
    {
        $this->actions[] = $action;
    }

    public function buildOccurence($occurs_at)
    {
        $result = new IdentifiedBankOperation($occurs_at, CashflowOperation::formatName($this->getName(), $occurs_at), $this->getAmount(), $this->getId());
        $result->setDeleteLink($this->getDeleteLink());
        $result->setEditLink($this->getEditLink());
        foreach ($this->getBankOperationActions() as $action) {
            $result->registerAction($action);
        }
        return $result;
    }
}