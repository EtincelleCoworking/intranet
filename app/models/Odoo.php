<?php

use Ripcord\Providers\Laravel\Ripcord;

class Odoo extends Ripcord
{
    const PRICELIST_COWORKERS = 2;
    const PRICELIST_PENDING = 3;

    public function __construct()
    {
        return parent::__construct(array(
            'url' => Config::get('etincelle.odoo_url') . "/xmlrpc/2",
            'db' => Config::get('etincelle.odoo_db'),
            'user' => Config::get('etincelle.odoo_username'),
            'password' => Config::get('etincelle.odoo_password')
        ));
    }

    protected function execute_kw($p1, $p2, $p3 = null, $p4 = null)
    {
        return $this->client->execute_kw($this->db, $this->uid, $this->password, $p1, $p2, $p3, $p4);
    }

    public function getKnownUsers()
    {
        $result = $this->execute_kw(
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
        return $this->execute_kw(
            'res.partner', 'create',
            array(array(
                'ref' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'barcode' => $this->getUserBarcode($id),
            )));
    }

    protected function getUserBarcode($id)
    {
        return sprintf('042%07d', $id);
    }

    public function updateUser($remote_id, $id, $name, $email, $phone)
    {
        return $this->execute_kw(
            'res.partner', 'write',
            array(array($remote_id), array(
                'ref' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'barcode' => $this->getUserBarcode($id),
            )));
    }

    public function getUnassignedOpenOrder($occurs_at)
    {
        $result = $this->execute_kw(
            'pos.order', 'search_read',
            array(
                array(
                    array('pricelist_id', '=', self::PRICELIST_PENDING),
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

    public function getOpenOrderProducts($occurs_at)
    {
        $result = $this->execute_kw(
            'pos.order.line', 'search_read',
            array(
                array(
                    array('pricelist_id', '=', self::PRICELIST_PENDING), // en compte
                    array('partner_id', '!=', false), // que ceux associés à un client
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

    public function createOrderFromPendingPosSales($occurs_at)
    {
        $data = $this->execute_kw(
            'pos.order', 'search_read',
            array(
                array(
                    array('pricelist_id', '=', self::PRICELIST_PENDING),
                    array('partner_id', '!=', false), // que ceux associés à un client
                    array('session_id.stop_at', '>=', "$occurs_at 00:00:00"),
                    array('session_id.stop_at', '<=', "$occurs_at 23:59:59"),
                )
            ),
            array(
                'fields' => array('date_order', 'partner_id', 'user_id', 'session_id', 'pricelist_id')
            )
        );

        $session_ids = array();

        foreach ($data as $item) {
            $session_ids[$item['session_id'][0]] = $item['session_id'][1];
        }
        if (count($session_ids) > 0) {
            $sessions_datas = $this->execute_kw(
                'pos.session', 'read',
                array_keys($session_ids),
                array(
                    'fields' => array('stop_at')
                )
            );

            /*
            print_r($data);
            print_r($session_ids);
            print_r($sessions_datas);
            //exit;
            */

            $sessions = array();
            foreach ($sessions_datas as $sessions_data) {
                $sessions[$sessions_data['id']] = $sessions_data['stop_at'];
            }

//print_r($data);exit;
            $orders = array();
            foreach ($data as $item) {
                $orders[$item['id']] = array(
                    'date_order' => $item['date_order'],
                    'partner_id' => $item['partner_id'][0],
                    'user_id' => $item['user_id'][0],
                    'confirmation_date' => $sessions[$item['session_id'][0]],
                );
            }
//print_r($orders);exit;
            if (count($orders) > 0) {
                $lines = $this->execute_kw(
                    'pos.order.line', 'search_read',
                    array(
                        array(
                            array('order_id', 'in', array_keys($orders)),
                        )
                    ),
                    array(
                        'fields' => array('qty', 'product_id', 'order_id', 'tax_ids')
                    )
                );
                $products = array();
                $items = array();
                foreach ($lines as $line) {
                    $partner_id = $orders[$line['order_id'][0]]['partner_id'];
                    $seller_id = $orders[$line['order_id'][0]]['user_id'];
                    $products[$line['product_id'][0]] = $line['product_id'][1];
                    $items[$partner_id][$seller_id][] = array(
                        'qty' => $line['qty'],
                        'product_id' => $line['product_id'][0],
                        'order_id' => $line['order_id'][0],
                        'tax_id' => $line['tax_ids'],
                    );
                }

//$confirmation_date = date('Y-m-d H:i:s');
//print_r($items); exit;
                foreach ($items as $partner_id => $lines_per_seller) {
//    var_dump($partner_id);
                    foreach ($lines_per_seller as $seller_id => $lines) {
                        $order_id = null;

                        foreach ($lines as $line) {
                            if (null == $order_id) {
                                $params = array(
                                    'date_order' => $orders[$line['order_id']]['confirmation_date'],
//                'currency_id' => $orders[$line['order_id']]['currency_id'],
                                    'partner_id' => $partner_id,
                                    'confirmation_date' => $orders[$line['order_id']]['confirmation_date'],
                                    'user_id' => $seller_id,
                                    'state' => 'sale',
                                    'pricelist_id' => self::PRICELIST_COWORKERS,
                                    'invoice_status' => 'to invoice',
                                );
                                $order_id = $this->execute_kw(
                                    'sale.order', 'create',
                                    array($params));
                                if (is_array($order_id)) {
                                    print_r($order_id);
                                    exit;
                                }
                                //print_r($order_id); exit;
                                printf("New Order: %s\n", $order_id);
                            }
                            $name = sprintf('%s (%s)', $products[$line['product_id']], date('d/m/Y H:i', strtotime($orders[$line['order_id']]['date_order'])));
                            $order_line_id = $this->execute_kw(
                                'sale.order.line', 'create',
                                array(array(
                                    'order_id' => $order_id,
                                    'product_id' => $line['product_id'],
                                    'product_uom_qty' => $line['qty'],
                                    'name' => $name,
                                    'tax_id' => array(array(6, 0, $line['tax_id'])),
                                )));
                            if (is_array($order_line_id)) {
                                print_r($order_line_id);
                            } else {
                                printf(" - New Order Line: %5s - %s\n", $order_line_id, $name);
                            }
                            //print_r($order_line_id);
                        }
                        //exit;
                    }
                }
            }
        }
    }

    public function getPosCustomersIds($start_at, $end_at = null)
    {
        if (null == $end_at) {
            $end_at = date('Y-m-d 23:59:59');
        }
        $orders = $this->execute_kw('pos.order', 'search_read',
            array(
                array(
                    array('partner_id', '!=', false),
                    array('date_order', '>=', $start_at),
                    array('date_order', '<=', $end_at)
                )
            ),
            array(
                'fields' => array('partner_id')
            )
        );

        $result = array();
        foreach ($orders as $order) {
            $result[$order['partner_id'][0]] = true;
        }

        $items = $this->execute_kw('res.partner', 'search_read',
            array(
                array(
                    array('id', 'in', array_keys($result))
                )
            ),
            array(
                'fields' => array('ref')
            )
        );

        $result = array();
        foreach ($items as $item) {
            $result[$item['ref']] = true;
        }
        return array_keys($result);
    }
}