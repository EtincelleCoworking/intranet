<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CoffeeShopInvoiceCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'coffee-shop:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        //region products

        // Mettre à jour à partir de l'URL https://intranet2021.etincelle-coworking.com/backend/coffeeshop/code
        $products = array (
            'fruits.clementine' =>
                array (
                    'name' => 'Clémentine',
                    'price' => '0.50',
                ),
            'fruits.other' =>
                array (
                    'name' => 'Pomme, Banane...',
                    'price' => '1.00',
                ),
            'drink' =>
                array (
                    'name' => 'Boisson fraiche',
                    'price' => '1.50',
                ),
            'snack' =>
                array (
                    'name' => 'Snack',
                    'price' => '1.00',
                ),
            'snack.bounty' =>
                array (
                    'name' => 'Bounty',
                    'price' => '1.00',
                ),
            'snack.twix' =>
                array (
                    'name' => 'Twix',
                    'price' => '1.00',
                ),
            'snack.lion' =>
                array (
                    'name' => 'Lion',
                    'price' => '1.00',
                ),
            'snack.kitkat' =>
                array (
                    'name' => 'KitKat',
                    'price' => '1.00',
                ),
            'snack.snickers' =>
                array (
                    'name' => 'Snickers',
                    'price' => '1.00',
                ),
            'snack.mars' =>
                array (
                    'name' => 'Mars',
                    'price' => '1.00',
                ),
            'snack.kinder-bueno' =>
                array (
                    'name' => 'Kinder Bueno',
                    'price' => '1.00',
                ),
            'snack.suchard.rocher' =>
                array (
                    'name' => 'Rocher Suchard (lait ou noir)',
                    'price' => '1.00',
                ),
            'snack.kitkat.chunky' =>
                array (
                    'name' => 'kitkat Chunky',
                    'price' => '1.00',
                ),
            'snack.oreo' =>
                array (
                    'name' => 'Milka Oreo',
                    'price' => '1.00',
                ),
            'snack.granola' =>
                array (
                    'name' => 'Granola',
                    'price' => '1.00',
                ),
            'snack.kitkat.white' =>
                array (
                    'name' => 'kitkat White',
                    'price' => '1.00',
                ),
            'pastry.credo.finger' =>
                array (
                    'name' => 'Madeleine Finger',
                    'price' => '2.00',
                ),
            'pastry.marina.browkie' =>
                array (
                    'name' => 'Browkie',
                    'price' => '2.00',
                ),
            'pastry.marina.2024-01-15-muffin-myrtille' =>
                array (
                    'name' => 'Muffin Myrtille',
                    'price' => '2.00',
                ),
            'pastry.marina.2024-01-29-lemon-cake' =>
                array (
                    'name' => 'Cake au citron',
                    'price' => '2.00',
                ),
            'pastry.marina.2024-01-22-banana-bread' =>
                array (
                    'name' => 'Banana Bread',
                    'price' => '2.00',
                ),
            'pastry.hordeaux.ugli-like-lemon-cake.small' =>
                array (
                    'name' => 'Ugli façon tarte au citron (petite)',
                    'price' => '2.00',
                ),
            'pastry.hordeaux.ugli-like-lemon-cake.large' =>
                array (
                    'name' => 'Ugli façon tarte au citron (grande)',
                    'price' => '4.00',
                ),
            'pastry.hordeaux.valentine-chocolate-manguo' =>
                array (
                    'name' => 'Tartelette chocolat, coeur mangue / orange',
                    'price' => '3.00',
                ),
            'pastry.hordeaux.small-tart-raspberry' =>
                array (
                    'name' => 'Tartelette Framboise',
                    'price' => '4.00',
                ),
            'pastry.hordeaux.raspberry-financier' =>
                array (
                    'name' => 'Financier Framboise',
                    'price' => '2.00',
                ),
            'pastry.mr-madeleine.madeleine' =>
                array (
                    'name' => 'Madeleine',
                    'price' => '1.50',
                ),
            'pastry.peche-mignon.brownie' =>
                array (
                    'name' => 'Brownie',
                    'price' => '1.50',
                ),
            'pastry.credo.carrot-cake' =>
                array (
                    'name' => 'Carrot Cake',
                    'price' => '2.00',
                ),
            'pastry.ohmycooks.cookie' =>
                array (
                    'name' => 'Cookie',
                    'price' => '4.00',
                ),
            'pastry.hordeaux.sable-diamant' =>
                array (
                    'name' => 'Sablé Diamant',
                    'price' => '1.00',
                ),
            'pastry.hordeaux.kiwi-lemon-tart' =>
                array (
                    'name' => 'Tartelette Kiwi Citron Vert',
                    'price' => '4.00',
                ),
            'pastry.hordeaux.raspberry-rubber-tarn' =>
                array (
                    'name' => 'Tartelette Rubarbe / Framboise',
                    'price' => '4.00',
                ),
            'pastry.hordeaux.sable-breton' =>
                array (
                    'name' => 'Sablé Breton',
                    'price' => '1.00',
                ),
            'pastry.hordeaux.small-tart-kiwi' =>
                array (
                    'name' => 'Tartelette Kiwi',
                    'price' => '4.00',
                ),
            'drinks.pago.ace' =>
                array (
                    'name' => 'Boisson ACE',
                    'price' => '1.50',
                ),
            'drinks.pago.mixed-fruits' =>
                array (
                    'name' => 'Jus Multifruits',
                    'price' => '1.50',
                ),
            'drinks.pago.orange-nectar' =>
                array (
                    'name' => 'Nectar d\'Orange',
                    'price' => '1.50',
                ),
            'drinks.fourgon.san-pellegrino' =>
                array (
                    'name' => 'San Pellegrino',
                    'price' => '1.50',
                ),
            'drinks.coca-cola.classic' =>
                array (
                    'name' => 'Coca-Cola Classic',
                    'price' => '1.50',
                ),
            'drinks.pampril.orange-juice' =>
                array (
                    'name' => 'Jus d\'Orange',
                    'price' => '1.50',
                ),
            'drinks.lipton.peach' =>
                array (
                    'name' => 'Lipton Pêche',
                    'price' => '1.50',
                ),
            'drinks.lipton.green' =>
                array (
                    'name' => 'Lipton Green',
                    'price' => '1.50',
                ),
            'drinks.coca-cola.fuzetea-peche' =>
                array (
                    'name' => 'Fuzetea Pêche',
                    'price' => '1.50',
                ),
            'drinks.pago.apple-juice' =>
                array (
                    'name' => 'Jus de Pomme',
                    'price' => '1.50',
                ),
            'drinks.orangina.schweppes-indian-tonic' =>
                array (
                    'name' => 'Schweppes Indian Tonic',
                    'price' => '1.50',
                ),
        );
        //endregion
        $vat = VatType::where('value', 20)->first();

        $users = DB::select(sprintf('SELECT user_id, concat(users.firstname, " ", users.lastname) as username, users.slug as user_slug, SUM(quantity) as pending_item_count 
            FROM coffeeshop_orders join users on users.id = coffeeshop_orders.user_id 
            WHERE (invoice_id IS NULL) AND (coffeeshop_orders.occurs_at < "%s") GROUP BY user_id', date('Y-m-01')));
        foreach ($users as $user) {
            $this->info($user->username);

            $organisation = Organisation::where('name', '=', $user->username)->first();
            if (null == $organisation) {
                $organisation = new Organisation();
                $organisation->name = $user->username;
                $organisation->country_id = Country::FRANCE;
                $organisation->accountant_id = $user->user_id;
                $organisation->save();

                $organisation_user = new OrganisationUser();
                $organisation_user->organisation_id = $organisation->id;
                $organisation_user->user_id = $user->user_id;
                $organisation_user->save();
            }

            $invoice = new Invoice();
            $invoice->user_id = $user->user_id;
            $invoice->created_at = new \DateTime();
            $invoice->organisation_id = $organisation->id;
            $invoice->type = 'F';
            $invoice->days = date('Ym');
            $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
            $invoice->address = $organisation->fulladdress;
            $invoice->date_invoice = new \DateTime();
            $invoice->deadline = new \DateTime(date('Y-m-d', strtotime('+1 month')));
            $invoice->expected_payment_at = $invoice->deadline;
            $invoice->save();

            $orderIndex = 0;
            $items = DB::select(sprintf('SELECT * FROM coffeeshop_orders WHERE user_id = %d and invoice_id IS NULL ORDER BY occurs_at DESC', $user->user_id));
            $items_to_update = [];
            $items_by_products = [];
            foreach ($items as $item) {
                $items_to_update[] = $item->id;
                if (!isset($items_by_products[$item->product_slug])) {
                    $items_by_products[$item->product_slug] = [];
                }
                $items_by_products[$item->product_slug][] = $item;
            }
            foreach ($items_by_products as $product_slug => $items) {
                $invoice_line = new InvoiceItem();
                $invoice_line->invoice_id = $invoice->id;
                $invoice_line->order_index = $orderIndex++;

                $invoice_line->text = sprintf('%s (%0.2f€): ', $products[$product_slug]['name'], $products[$product_slug]['price']);
                $lines = [];
                $sum = 0;
                foreach ($items as $item) {
                    $sum += $item->quantity;
                    if (1 == $item->quantity) {
                        $lines[] = date('d/m/Y H:i', strtotime($item->occurs_at));
                    } else {
                        $lines[] = sprintf('%s (%s)', date('d/m/Y H:i', strtotime($item->occurs_at)), $item->quantity);
                    }
                }
                $invoice_line->text .= implode(', ', $lines);
                $invoice_line->amount = $sum * (float)$products[$item->product_slug]['price'] / 1.2;

                $invoice_line->vat_types_id = $vat->id;
                $invoice_line->ressource_id = Ressource::TYPE_EXCEPTIONNAL;
                $invoice_line->save();
            }
            DB::statement(sprintf('UPDATE coffeeshop_orders SET invoice_id = %d WHERE id in (%s)', $invoice->id, implode(', ', $items_to_update)));
            $this->output->writeln(sprintf('La facture %s a été créée', $invoice->ident));
        }
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
