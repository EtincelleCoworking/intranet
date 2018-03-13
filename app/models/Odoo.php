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
}