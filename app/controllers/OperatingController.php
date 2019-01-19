<?php

/**
 * Operating Controller
 */
class OperatingController extends BaseController
{
    public function index()
    {
        $params = array();
        $params['rooms'] = array(
            array('name' => 'Salle 6-8 personnes'),
            array('name' => 'Salle 10-12 personnes'),
            array('name' => 'Salle Ouganda'),
            array('name' => 'Salon Colombie'),
            array('name' => 'Salle de conférence'),
        );
        return View::make('operating.index', $params);
    }

}
