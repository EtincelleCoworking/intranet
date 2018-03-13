<?php

use Ripcord\Providers\Laravel\Ripcord;

class Odoo extends Ripcord
{

    public function __construct()
    {
        return parent::__construct(array(
            'url' => Config::get('etincelle.odoo_url') . "/xmlrpc/2",
            'db' => Config::get('etincelle.odoo_db'),
            'user' => Config::get('etincelle.odoo_username'),
            'password' => Config::get('etincelle.odoo_password')
        ));
    }

    public function getKnownUsers()
    {
        $result = $this->client->execute_kw($this->db, $this->uid, $this->password,
            'res.partner', 'search_read',
            array(
                array(
                    array('is_company', '=', false)
                )
            ),
            array(
                'fields' => array('name', 'phone', 'ref', 'email')
            )
        );
        return $result;
    }

    public function createUser($id, $name, $email, $phone)
    {
        return $this->client->execute_kw($this->db, $this->uid, $this->password,
            'res.partner', 'create',
            array(array(
                'ref' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            )));
    }

    public function updateUser($remote_id, $id, $name, $email, $phone)
    {
        return $this->client->execute_kw($this->db, $this->uid, $this->password,
            'res.partner', 'write',
            array(array($remote_id), array(
                'ref' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            )));
    }

    public function getUnassignedOpenOrder($occurs_at)
    {
        $result = $this->client->execute_kw($this->db, $this->uid, $this->password,
            'pos.order', 'search_read',
            array(
                array(
                    array('pricelist_id', '=', 3),
                    array('partner_id', '=', false),
                    array('session_id.stop_at', '>=', "$occurs_at 00:00:00"),
                    array('session_id.stop_at', '<=', "$occurs_at 23:59:59"),
                )
            ),
            array(
                'fields' => array('name')
            )
        );
        return $result;
    }
}