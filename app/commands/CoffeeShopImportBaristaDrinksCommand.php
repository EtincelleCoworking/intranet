<?php

use Illuminate\Console\Command;

class CoffeeShopImportBaristaDrinksCommand extends Command
{
    const FIELD_USER = 0;
    const FIELD_DATE = 1;
    const FIELD_QUANTITY = 3;
    const FIELD_PRODUCT = 4;
    const FIELD_UNIT_PRICE = 5;
    const FIELD_ADDON = 7;
    const FIELD_COMMENT = 8;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'coffee-shop:import-barista-drinks';

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
        $content = $this->getContent();
        $data = [];
        $users = [];
        $addons = [];
        foreach (explode("\n", $content) as $line) {
            if ($line) {
                //$this->output->writeln('');
                $this->output->writeln('<info>' . $line . '</info>');

                $tokens = explode("\t", $line);

                $addon_comment = isset($tokens[self::FIELD_COMMENT]) ? trim($tokens[self::FIELD_COMMENT]) : null;

                if ($addon_comment === 'Offert SH') {
                    $this->output->writeln('Offert / ignoré facturation');
                } else {
                    $customer_name = $tokens[self::FIELD_USER];
                    if ($customer_name !== 'SUIVI CONSO GRATUITE') {
                        if (isset($users[$tokens[0]])) {
                            $user_id = $users[$customer_name];
                        } else {
                            $user_id = $this->getUserId($customer_name);
                            if ($user_id) {
                                $users[$tokens[0]] = $user_id;
                            } else {
                                //$this->output->writeln(sprintf('Utilisateur inconnu : [%s]', $tokens[0]));
                                $user = User::where('slug', str_replace(' ', '.', strtolower($customer_name)))->first();
                                if ($user) {
                                    //  $this->output->writeln('');
                                    $this->output->writeln(sprintf("<error>case '%s': return %d;</error>", $customer_name, $user->id));
                                    $users[$customer_name] = $user->id;
                                    //  $this->output->writeln('');
                                } else {
                                    $this->output->writeln(sprintf("<error>case '%s': return null;</error>", $customer_name));
                                    $users[$customer_name] = false;
                                    return false;
                                }
                            }
                        }
                        $quantity = $tokens[self::FIELD_QUANTITY];
                        $occurs_at = preg_replace('|^([0-9]{2})/([0-9]{2})/([0-9]{4})$|', '$3-$2-$1', $tokens[self::FIELD_DATE]);
                        $product = \Illuminate\Support\Str::slug($tokens[self::FIELD_PRODUCT]);

                        $product_price = $this->getProductPricing($product);
                        if (false === $product_price) {
                            $product_price = 0;
                            $this->output->writeln(sprintf("<error>Produit inconnu : [%s]</error>", $product));
                            return false;
                        }
                        $price = $tokens[5];
                        $price = preg_replace('/^([0-9]+)(:?,([0-9]+))? .*$/', '$1.$3', trim($price)) . '00';
                        $addon_price = 0;
                        if ($price != $product_price) {
                            $addon_price = (float)$price - (float)$product_price;
                            $this->output->writeln(sprintf('<info>[%s] => %s (= %s ?) - addon : %s</info>', $tokens[self::FIELD_UNIT_PRICE], $price, $product_price, $addon_price));
                            if ($addon_price < 0) {
                                $this->output->writeln(sprintf('<error>Prix négatif [%s] </error>', $addon_price));
                            }
                        }

                        $addon = isset($tokens[self::FIELD_ADDON]) ? trim($tokens[self::FIELD_ADDON]) : null;
                        if ($addon) {
                            if (!isset($addons[$addon])) {
                                $addons[$addon] = $addon_price;
                            } else {
                                if ($addons[$addon] == $addon_price) {
                                    // ok
                                } else {
                                    $this->output->writeln(sprintf('<error>Différence de prix addon [%s] connu : %s, actuel : %s </error>', $addon, $addons[$addon], $addon_price));
                                }
                            }
                        }

                        $this->output->writeln(sprintf('User : %s', $user_id));
                        $this->output->writeln(sprintf('Quantity : %s', $quantity));
                        $this->output->writeln(sprintf('OccursAt : %s', $occurs_at));
                        $this->output->writeln(sprintf('Product : %s', $product));
                        $this->output->writeln(sprintf('Addon : %s', $addon));
                        $this->output->writeln(sprintf('addon_price : %s', $addon_price));
                        $this->output->writeln(sprintf('addon_comment : %s', $addon_comment));
                        $this->output->writeln(sprintf('price : %s', $product_price));
                        /*
                                            $data[] = [
                                                'user_id' => $user_id,
                                                'quantity' => $quantity,
                                                'occurs_at' => $occurs_at,
                                                'product' => $product,
                                                'addon' => $addon,
                                                'addon_price' => $addon_price,
                                                'addon_comment' => $addon_comment,
                                                'price' => $product_price,
                                            ];*/
                        $order = new CoffeeShopOrder();
                        $order->user_id = $user_id;
                        $order->quantity = $quantity;
                        $order->occurs_at = $occurs_at;
                        $order->product_slug = $product;
                        $order->product_addon = $addon;
                        $order->product_addon_cost = $addon_price;
                        $order->product_addon_comment = $addon_comment;
                        if (!$this->option('dry-run')) {
                            $order->save();
                        }
                    }
                }
            }
        }
        dump($addons);
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
        return array(
            array('dry-run', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, '', null),
        );
    }

    private function getUserId($name)
    {
        switch ($name) {
            case 'AMINE EL AZIZI':
            case 'AMINE ELAZIZI':
                return 3941;
            case 'BENOIT COUX':
                return 4324;
            case 'CEDRIC BOUCHE':
                return 4307;
            case 'MELINE BOUYSSI':
                return 5974;
            case 'NATHALIE GRENET':
                return 99;
            case 'VALENTIN RENAUD':
                return 6383;
            case 'ADRIANA':
            case 'ADRIANA ROA':
                return 5999;
            case 'AGUSTINA':
            case 'AGUSTINA WEBER':
                return 6294;
            case 'ALEXANDRE':
            case 'ALEXANDRE BRUN':
                return 5723;
            case 'BARNABE LEVARD':
                return 5379;
            case 'MARIE-LAURE MOENS':
                return 6631;
            case 'MATTHIEU CROUZET':
                return 5159;
            case 'UGO DE LUCA':
                return 4341;
            case 'VIRGINIE DEL RIEU':
                return 4028;
            case 'LAURE SARDELLA':
                return 6781;
            case 'PAULINE SPINAZZE':
                return 4424;
            case 'ADRIEN MORQUE':
                return 6139;
            case 'JOCHEN GRUNBECK':
                return 6717;
            case 'MATTHEW WALKER':
                return 6687;
            case 'OUARDIA EL BONNOUHI':
                return 6225;
            case 'VLAD CERISIER':
                return 5824;
            case 'ANGELIQUE FOUIX':
                return 6471;
            case 'CELINE FABRE':
                return 6452;
            case 'FABRICE LUCAS':
                return 5973;
            case 'JEAN CIAPA':
                return 1663;
            case 'PHILIPPE LANDES':
                return 5936;
            case 'VALERIE HAMEAU':
                return 6470;
            case 'ANASTASIA DE SANTIS':
                return 4731;
            case 'BAPTISTE MATHUS':
                return 452;
            case 'LAETITIA RUAULT DURAND':
                return 2805;
            case 'MARGAUX DEROSIER':
                return 5312;
            case 'MAXIME LEVEAU':
                return 5865;
            case 'BLANDINE':
            case 'BLANDINE DE CHALLEMAISON':
                return 483;
            case 'KRISTELL THOMAS':
                return 5409;
            case 'LUCILLE BISBAU':
                return 6562;
            case 'AYMERIC JOUON':
                return 6829;
            case 'JOHAN RITTERSHAUS':
                return 5711;
            case 'KEVIN LEMELE':
                return 6865;
            case 'MOUAD BELGHITI':
                return 6091;
            case 'ELENA PERROUIN':
                return 121;
            case 'JACQUES DUTILLEUX':
                return 6306;
            case 'PAULINE SARDA':
                return 3406;
            case 'PIERRE HAMEL':
                return 6597;
            case 'ADRIEN BEUDIN':
                return 6868;
            case 'PIERRE DE DUFOURCQ':
                return 3824;
            case 'EINAT ARGON':
                return 6871;
            case 'JERMAIN NJEMANZE':
                return 6876;
            case 'MARTIAL':
            case 'MARTIAL MONTRICHARD':
                return 5886;
            case 'OLIVIER REVIAL':
                return 6336;
            case 'SANCIE VANNEAUD':
                return 6873;
            case 'BENJAMIN CORRET':
                return 5996;
            case 'HELENE FABRE':
                return 3625;
            case 'ARNAUD THOMAS-SERVAIS':
                return 4922;
            case 'GREGOIRE CORBIERE':
                return 6090;
            case 'LILA RAMALINGOM':
                return 5330;
            case 'ALEXIS BASSET':
                return 6308;
            case 'BENJAMIN BOUVET':
                return 6491;
            case 'EMMANUELLE VAN DEN STEEN':
                return 5360;
            case 'JORDAN LLIDO':
                return 359;
            case 'LUCIE SCHAMING':
                return 6503;
            case 'AMEL GUEDRI':
                return 6522;
            case 'ANTHONY FELIN':
                return 1379;
            case 'BENJAMIN LEVESQUE':
                return 5396;
            case 'CELINE LASBATX':
                return 5156;
            case 'JULIE DENAT':
                return 6661;
            case 'CLAIRE BELLOC':
                return 6843;
            case 'JULIE COUSSE':
                return 6680;
            case 'YANN KALECINSKI':
                return 4445;
            case 'CHANTAL PERDIGAU':
                return 4597;
            case 'DAVID BONNET':
                return 6128;
            case 'MARINA CASALE':
                return 3509;
            case 'FRANCOIS BREMOND':
                return 3665;
            case 'LOUIS MELLIORAT':
                return 6911;
            case 'PHILIPPE ROLAND':
                return 6761;
            case 'SEBASTIEN FLOCHLEY':
            case 'SEBASTIEN FLOCHLAY':
                return 5740;
            case 'CHARLES GIAFFERI':
                return 4323;
            case 'ABDEL HALIMI':
                return 6163;
            case 'CHRISTOPHE BOUE':
                return 598;
            case 'CORENTIN DOUAY':
                return 5922;
            case 'LUCIEN THIRIET':
                return 6921;
            case 'CASSANDRA BONNAFOUS':
                return 4834;
            case 'ANAEL MEGNA':
                return 5900;
            case 'ELIA MONGUILLON':
                return 6931;
            case 'ALEXIS DAMIENS':
                return 6645;
            case 'FADEL DIENE':
                return 6307;
            case 'FRANCOIS HELLOCO':
                return 76;
            case 'JEANNE ROBIN':
                return 6464;
            case 'SAVANNAH SERVANT':
                return 6942;
            case 'VALERIE ALASLUQUETAS':
                return 6944;
            case 'SOLENE ROSSARD':
                return 5582;
            case 'CLAIRE HADJADJ':
                return 6559;
            case 'ELODIE ALVES':
                return 5662;
            case 'FLORIAN DEVASSE':
                return 5738;
            case 'CLARA MANHES':
                return 6959;
            case 'GLORIA FRADIN':
                return 3894;
            case 'THAÏSS OYINI':
                return 6947;
            case 'JUNIOR KABORE':
                return 3974;
            case 'MELODIE TYLER':
                return 4337;
            case 'ASHLEY TSE':
                return 5885;
            case 'CLAIRE DHOOSCHE':
                return 6991;
            case 'GAUTHIER JOLLY':
                return 6415;
            case 'MAEL VALAIS':
                return 5965;
            case 'QUENTIN PANLOUP':
                return 6870;
            case 'SOFIE LEON':
                return 6483;
            case 'CASSANDRE DORET':
                return 7004;
            case 'CHRISTELLE LAGAE':
                return 6912;
            case 'THOMAS LECHEVALIER':
                return 4120;
            case 'MAELIA LEGRAND':
                return 6998;
            case 'EMMANUELLE BIADI-COMET':
                return 417;
            case 'ERIC GUIN':
                return 1307;
            case 'DAMIEN MATHIEU':
                return 11;
            case 'IRIS BORRUT':
                return 4354;
            case 'CLEMENTINE CABROL':
                return 4645;
            case 'MANU DEJEAN':
                return 335;
            case 'ARIADNA MATAS':
                return 5353;
            case 'DAVID DAIGNAN':
                return 6723;
            case 'EDGAR RODRIGUES':
                return 7049;
            case 'MARGAUX ARTUSO':
                return 7051;
            case 'MAX LEVER':
                return 6346;
            case 'NICOLAS NAUDY':
                return 7043;
            case 'CHADI LAJMI':
                return 7021;
            case 'MARYLENE LAURENT':
                return 6217;
            case 'AURELIE PICHOT':
                return 6211;
            case 'SOPHIE JAMAIN':
                return 7002;
            case 'JULIE CARTIGNI':
            case 'JULIE CARTIGNY':
                return 7034;
            case 'SARAH VIGUIE':
                return 7073;
            case 'JULIEN CARVAJAL':
                return 7075;
            case 'CAROLINE TERGEMINA':
                return 7074;
            case 'MELANIE BESSAGNET':
                return 990;
            case 'FABRICE RAKOTONARIVO':
                return 5277;
            case 'CLARISSE LOU':
                return 7096;
            case 'LOUIS JACQUES':
                return 7095;
            //case 'LOUIS GOUEZE': return null;
            case 'LESLIE ROUZIER':
                return 7078;
            //case 'SARAH VIGUIE': return null;
            //case 'ANNE-LISE H.': return null;
            //case 'CAMILLE C.': return null;
            //case '?': return null;
            // case 'SOHAIR': return null;
            // case 'CAROLINE TERGEMINA': return null;
            //case 'JULIEN CARVAJAL': return null;
            //case '        ?': return null;
            // case 'IVANA SAILHAN': return null;
            // case 'JULIE CARTIGNI': return null;
            case 'BENJAMIN GUILHEMJOUAN':
                return 5569;
            case 'COLLEEN HANRIOT':
                return 6017;
            case 'BENOIT EL AMRANI':
                return 4301;
            case 'JEAN-PHILIPPE KHA':
                return 4314;
            case 'MARION RATIER':
                return 7041;
            case 'LEANE DOMERGUE':
                return 7099;
            case 'HELENE BERRIER':
                return 7125;
            case 'CATHERINE DORE':
                return 6575;
            case 'GREGORY ESTRADE':
                return 145;
            case 'CORALINE RASSET':
                return 5680;
            case 'BAPTISTE VILLENEUVE':
                return 7202;
            case 'ANAIS EL AOUD':
                return 6195;
            case 'SAMANTHA HARRACA':
                return 5759;
            //case 'EFIARETA EHAMELO': return null;
            case 'ESTEVE PINYOL':
                return 2571;
            case 'JEAN-MARC D\'ANDRIA':
                return 745;
            case 'MORGAN URIEN':
                return 6697;
            case 'LEO VINCENT':
                return 6077;
            case 'GERALD GOUNOT':
                return 7134;
            case 'AMELIE BIMONT':
                return 6826;
            case 'CHERIF MILI':
                return 2993;
            case 'ROBERTO PASQUA':
                return 6386;
            case 'EDDINE SAIDI':
                return 7101;
            case 'ALIA DOYEN':
                return 7180;
            case 'LAETITIA MONTRICHARD':
                return 7126;
            case 'ADRIEN CRUCIFIX':
                return 6155;
            case 'MELODIE DOUGNAC':
                return 4568;
            case 'AMAURY RAVENEL':
            case 'AMAURY RAVANEL':
                return 2555;
            case 'NICOLAS SAUNIER':
                return 5930;
            case 'CINDY HERAUD':
                return 7160;
            case 'AUDE PIERRE':
                return 4829;
            case 'CHRISTIAN RAKOTONDRAINIBE': return 7247;
            case 'MARIA CHOUPPARD': return 7211;
            case 'CECILE BARTHES': return 7250;
            case 'DOMITILLE GALLI': return 7159;
            case 'SARA TISSENIER': return 7228;
            case 'JULIEN COUTURIER': return 419;
            case 'MOHAMED ELADL': return 5926;
            case 'IMENE THAMRI': return 6176;
            case 'LOUIS ULMER': return 7240;
            case 'MATHIEU FELIX': return 6643;
            case 'ELSA CARDINAUD': return 7229;
            case 'MATHIEU LECOQ': return 1018;case 'DIDIER LAHAY': return 7190;
            default :
                return false;
        }
    }

    protected function getProductPricing($name)
    {
        $catalog = [
            'Aerocano' => 1.5, // L pas d'option
            'Americano double shot' => 0.5, // transformer en produit à 0 + option shot - pas d'option taille
            'Babyccino' => 0.5, // taille normal, option L, XL, XXL
            'Café frappé' => 1.5, // taille normal, pas d'option taille
            'Cappuccino' => 1.5, //  taille normal, pas d'option taille
            'Cappuccino avoine' => 1.5,//  taille normal, pas d'option taille
            'Cappuccino glacé' => 2,//  taille normal, pas d'option taille
            'Chaï avoine' => 2,// taille normal, L/XL/XXL
            'Chaï glacé' => 2.5,// taille L - XL, XXL possible
            'Chaï latte' => 2,// taille normal, L/XL/XXL
            'Chocolat chaud' => 0,// taille normal, L/XL/XXL
            'Chocolat glacé' => 0.5,// taille L - XL/XXL possible
            'Dirty chaï latte' => 2.5, // pas de déclinaison taille
            'Double affogato' => 2.5,// pas de déclinaison taille
            'Double espresso' => 0.5,// pas de déclinaison taille
            'Double macchiato/noisette' => 1.5,// pas de déclinaison taille
            'Espresso tonic' => 2,// pas de déclinaison taille
            'Flat white' => 2,// pas de déclinaison taille
            'Genmaïcha' => 2,// pas de déclinaison taille
            'Granola bowl' => 4.5,// pas de déclinaison taille
            'Latte' => 2, // pas de déclinaison taille
            'Latte glace' => 2.5, // taille XL - XXL possible
            'Latte glace (caramel)' => 2.5,// taille XL - XXL possible
            'Latte glacé (cookie)' => 2.5,// taille XL - XXL possible
            'Latte glacé (noisette)' => 2.5,// taille XL - XXL possible
            'Latte glacé (vanille)' => 2.5,// taille XL - XXL possible
            'Latte glacé avoine' => 2.5,// taille XL - XXL possible
            'Latte glacé vietnamien' => 2.5,// taille XL - XXL possible
            'Latte macchiato' => 2.5,// pas de déclinaison taille
            'Latte macchiato (caramel)' => 2.5,// pas de déclinaison taille
            'Latte macchiato (cookies)' => 2.5,// pas de déclinaison taille
            'Latte macchiato (noisette)' => 2.5,// pas de déclinaison taille
            'Latte macchiato (vanille)' => 2.5,// pas de déclinaison taille
            'Macchiato/noisette' => 1,// pas de déclinaison taille
            'Macchiato/noisette avoine' => 1,// pas de déclinaison taille
            'Matcha latte' => 2,// taille normal, L/XL/XXL
            'Matcha latte avoine' => 2,// taille normal, L/XL/XXL
            'Matcha latte glace' => 2.5,// taille L - XL, XXL possible
            'Matcha soda' => 2.5,// taille XL - XXL possible
            'Moca' => 1,// pas de déclinaison taille
            'Mocaccino' => 2,// pas de déclinaison taille
            'Mocaccino (caramel)' => 2,// pas de déclinaison taille
            'Mocaccino avoine' => 2,// pas de déclinaison taille
            'Mocaccino glace' => 2.5,// taille XL - XXL possible
            'Moon milk' => 2,// pas de déclinaison taille
            'Thé matcha' => 2,// pas de déclinaison taille
            'Vanilla cream cold brew' => 2,// pas de déclinaison taille
            'Infusion ginger lemon' => 0.5,// pas de déclinaison taille
            'Chicoryccino' => 1.5,// ?
            'Immunity Shot' => 1.0,// ?
            'Pumpkin Spice Latte' => 3.0,// ?
            'Chicoree' => 0.5,// ?
            'White matcha latte' => 2.5,// ?
            'Tiramisu latte' => 3,// ?
            'Café viennois' => 1.5,// ?
            'Ours blanc' => 2.5,// ?
//pago*
//bounty*
//lion*
        ];
        $products = [];
        foreach ($catalog as $product_name => $price) {
            $products[\Illuminate\Support\Str::slug($product_name)] = $price;
        }
        if (isset($products[\Illuminate\Support\Str::slug($name)])) {
            return $products[\Illuminate\Support\Str::slug($name)];
        }
        return false;
    }

    private function getContent()
    {
        return 'MAELIA LEGRAND	03/02/2025		2	CHICORYCCINO	2,00 €	4,00 €	L																					
MAELIA LEGRAND	04/02/2025		2	CHICORYCCINO	2,00 €	4,00 €	L																					
MAELIA LEGRAND	05/02/2025		3	CHICORYCCINO	2,00 €	6,00 €	L																					
MAELIA LEGRAND	11/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L																					
MAELIA LEGRAND	11/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L																					
MAELIA LEGRAND	12/02/2025		2	CHICORYCCINO	2,00 €	4,00 €	L																					
MAELIA LEGRAND	13/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L																					
MAELIA LEGRAND	13/02/2025		1	CHICORYCCINO	1,50 €	1,50 €	L																					
MAELIA LEGRAND	17/02/2025		3	CHICORYCCINO	2,00 €	6,00 €	L																					
MAELIA LEGRAND	18/02/2025		2	CHICORYCCINO	2,00 €	4,00 €	L																					
MAELIA LEGRAND	19/02/2025		3	CHICORYCCINO	2,00 €	6,00 €	L																					';
    }

}
