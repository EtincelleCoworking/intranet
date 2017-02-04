<?php

class BankOperationAction
{
    protected $url;
    protected $link_class;
    protected $icon_class;
    protected $help;
    protected $target = '_self';

    public function __construct($url, $link_class, $icon_class, $help = '')
    {
        $this->url = $url;
        $this->link_class = $link_class;
        $this->icon_class = $icon_class;
        $this->help = $help;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getLinkClass()
    {
        return $this->link_class;
    }

    public function getIconClass()
    {
        return $this->icon_class;
    }

    public function getHelp()
    {
        return $this->help;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($value)
    {
        $this->target = $value;
    }
}