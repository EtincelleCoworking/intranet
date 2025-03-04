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

                if (in_array($addon_comment, array('Offert SH', '(offert) JOURNEE D\'ESSAI', '(OFFERT)PRIVAT. OUGANDA'))) {
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
                        $order->product_slug = 'hot-drinks.'.$product;
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
        return 'AUDE PIERRE	10/01/2025		2	FRUIT*	0,50 €	1,00 €		aude.pierre@acolad.com	
BAPTISTE VILLENEUVE	30/01/2025	METAVONICS	1	KIT KAT*	1,00 €	1,00 €			
ERIC GUIN	03/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
ERIC GUIN	03/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
LEANE DOMERGUE	03/02/2025		3	LATTE	2,00 €	6,00 €			
MARION RATIER	03/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	03/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
NATHALIE GRENET	03/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	03/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/02/2025		8	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/02/2025		4	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/02/2025		5	espresso	0,00 €	0,00 €			
VALERIE HAMEAU	03/02/2025		2	LATTE	2,00 €	4,00 €			
BARNABE LEVARD	04/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLAIRE BELLOC	04/02/2025	1000CAFES	1	LATTE	2,00 €	2,00 €			
ELENA PERROUIN	04/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
GERALD GOUNOT	04/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
GLORIA FRADIN	04/02/2025	SPARK	1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
JEAN CIAPA	04/02/2025		1	MOCACCINO	2,00 €	2,00 €			
JEAN-MARC D\'ANDRIA	04/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
JEAN-MARC D\'ANDRIA	04/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
LAETITIA RUAULT DURAND	04/02/2025		1	CAFE VIENNOIS	1,50 €	1,50 €			
LAURE SARDELLA	04/02/2025	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LAURE SARDELLA	04/02/2025	TECHNIA	1	INFUSION GINGER LEMON	0,50 €	0,50 €			
MATTHIEU CROUZET	04/02/2025		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
MELINE BOUYSSI	04/02/2025	METAVONICS	1	MOCACCINO	2,50 €	2,50 €	L		
MELODIE DOUGNAC	04/02/2025	SPARK	1	CHICORYCCINO	2,00 €	2,00 €	L		
OUARDIA EL BONNOUHI	04/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
SOLENE ROSSARD	04/02/2025		1	LATTE	2,00 €	2,00 €			
SUIVI CONSO GRATUITE	04/02/2025		11	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	04/02/2025		16	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	04/02/2025		7	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	04/02/2025		6	CHOCOLAT CHAUD	0,00 €	0,00 €			
MARION DESCLAUX	04/02/2025		1	MOCA	1,00 €	1,00 €			
ALEXANDRE BRUN	05/02/2025		1	GRANOLA BOWL	4,50 €	4,50 €			
BAPTISTE MATHUS	05/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CHANTAL PERDIGAU	05/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
CHRISTELLE LAGAE	05/02/2025		2	DOUBLE ESPRESSO	0,50 €	1,00 €			
ERIC GUIN	05/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
LAURE SARDELLA	05/02/2025		1	ESPRESSO TONIC	2,00 €	2,00 €			
LAURE SARDELLA	05/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
LEANE DOMERGUE	05/02/2025		1	LATTE	2,00 €	2,00 €			
MATTHIEU CROUZET	05/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MATTHIEU CROUZET	05/02/2025		1	LATTE MACCHIATO	2,50 €	2,50 €			
NATHALIE GRENET	05/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	05/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
SOLENE ROSSARD	05/02/2025		1	LATTE	2,00 €	2,00 €			
SOPHIE JAMAIN	05/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
SOPHIE JAMAIN	05/02/2025		1	LATTE	2,00 €	2,00 €			
SUIVI CONSO GRATUITE	05/02/2025		6	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	05/02/2025		10	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	05/02/2025		5	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	05/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
AGUSTINA WEBER	06/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	06/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CHRISTOPHE BOUE	06/02/2025	BRYO	1	OURS BLANC	2,50 €	2,50 €		FABRICE	
CHRISTOPHE BOUE	06/02/2025	BRYO	2	LATTE MACCHIATO	2,50 €	5,00 €			
ERIC GUIN	06/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
MELINE BOUYSSI	06/02/2025	METAVONICS	1	MOCACCINO	2,50 €	2,50 €	L		
OUARDIA EL BONNOUHI	06/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
SARAH VIGUIE	06/02/2025		1	LATTE	2,00 €	2,00 €			
SOPHIE JAMAIN	06/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
CHRISTOPHE BOUE	06/02/2025	BRYO	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		FABRICE	
CHRISTOPHE BOUE	06/02/2025	BRYO	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		MARC	
SARA TISSENIER	06/02/2025		1	LATTE	2,00 €	2,00 €			
SUIVI CONSO GRATUITE	06/02/2025		4	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	06/02/2025		25	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	06/02/2025		6	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	06/02/2025		8	CHOCOLAT CHAUD	0,00 €	0,00 €			
GERALD GOUNOT	06/02/2025		1	MOCACCINO	2,00 €	2,00 €			
BLANDINE DE CHALLEMAISON	06/02/2025		1	MOCACCINO	2,00 €	2,00 €			
PIERRE DE DUFOURCQ	06/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	06/02/2025	METAVONICS	1	CHICOREE	0,50 €	0,50 €			
SOPHIE JAMAIN	07/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
OUARDIA EL BONNOUHI	07/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CEDRIC BOUCHE	07/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	07/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MELINE BOUYSSI	07/02/2025	METAVONICS	1	MOCACCINO	2,00 €	2,00 €	L		
CEDRIC BOUCHE	07/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	07/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ERIC GUIN	07/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	07/02/2025	METAVONICS	1	CHICOREE	0,50 €	0,50 €			
CINDY HERAUD	07/02/2025		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
SOPHIE JAMAIN	07/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	07/02/2025		8	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	07/02/2025		20	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	07/02/2025		10	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	07/02/2025		5	boisson de la semaine	0,00 €	0,00 €			
BAPTISTE MATHUS	10/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CHANTAL PERDIGAU	10/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	10/02/2025	METAVONICS	1	MOCACCINO	2,00 €	2,00 €	L		
LAETITIA MONTRICHARD	10/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ANGELIQUE FOUIX	10/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
VALERIE HAMEAU	10/02/2025		1	LATTE	2,00 €	2,00 €			
CLARA MANHES	10/02/2025	YUKAN	1	LATTE	2,00 €	2,00 €			
VALERIE HAMEAU	10/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
BENOIT COUX	10/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
DAVID BONNET	10/02/2025		2	CAPPUCCINO	1,50 €	3,00 €			
BARNABE LEVARD	10/02/2025		1	MOCACCINO	2,00 €	2,00 €			
ANGELIQUE FOUIX	10/02/2025		1	MOCACCINO	2,00 €	2,00 €			
MELODIE DOUGNAC	10/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
PHILIPPE LANDES	10/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L		
VALERIE ALASLUQUETAS	10/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VALERIE HAMEAU	10/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	10/02/2025		6	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	10/02/2025		20	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	10/02/2025		10	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	10/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
ELENA PERROUIN	11/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
BAPTISTE MATHUS	11/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
MATTHIEU CROUZET	11/02/2025		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
LAETITIA MONTRICHARD	11/02/2025	METAVONICS	1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MELINE BOUYSSI	11/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
NATHALIE GRENET	11/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CASSANDRA BONNAFOUS	11/02/2025		1	CHOCOLAT GLACE	0,50 €	0,50 €			
LEANE DOMERGUE	11/02/2025	CONTAKT	2	LATTE	2,00 €	4,00 €			
BARNABE LEVARD	11/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
GLORIA FRADIN	11/02/2025	SPARK	1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	11/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	11/02/2025		4	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	11/02/2025		13	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	11/02/2025		10	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	11/02/2025		9	boisson de la semaine	0,00 €	0,00 €			
MELINE BOUYSSI	11/02/2025	METAVONICS	1	CHICOREE	0,50 €	0,50 €			
LAETITIA RUAULT DURAND	11/02/2025		1	MOCACCINO	2,00 €	2,00 €			
NATHALIE GRENET	11/02/2025		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
CLARA MANHES	11/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MAX LEVER	11/02/2025		1	CHICOREE	0,50 €	0,50 €			
CHANTAL PERDIGAU	12/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MARION RATIER	12/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MOUAD BELGHITI	12/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MELINE BOUYSSI	12/02/2025	METAVONICS	1	MOCACCINO	2,50 €	2,50 €	L		
AGUSTINA WEBER	12/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
CLARA MANHES	12/02/2025		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
HELENE FABRE	12/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ERIC GUIN	12/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BENOIT COUX	12/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ERIC GUIN	12/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	12/02/2025		1	LATTE MACCHIATO	2,50 €	2,50 €			
GERALD GOUNOT	12/02/2025		1	LATTE	2,00 €	2,00 €			
MARION RATIER	12/02/2025		1	CHAÏ LATTE	2,00 €	2,00 €			
JULIEN CARVAJAL	12/02/2025		1	THE MATCHA	2,00 €	2,00 €			
SUIVI CONSO GRATUITE	12/02/2025		8	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	12/02/2025		6	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	12/02/2025		20	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	12/02/2025		8	espresso	0,00 €	0,00 €			
BENOIT COUX	13/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CHRISTOPHE BOUE	13/02/2025	BRYO	2	LATTE MACCHIATO	2,50 €	5,00 €			
SOPHIE JAMAIN	13/02/2025		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
CHRISTELLE LAGAE	13/02/2025	1000CAFES	1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ERIC GUIN	13/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	13/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
UGO DE LUCA	13/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	13/02/2025		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
CECILE BARTHES	13/02/2025	METAVONICS	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MELINE BOUYSSI	13/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
BARNABE LEVARD	13/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
CLAIRE BELLOC	13/02/2025	1000CAFES	1	boisson de la semaine	0,50 €	0,50 €	SHOT SUPP		
FRANCOIS HELLOCO	13/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
PIERRE HAMEL	13/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
BENOIT COUX	13/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	13/02/2025		7	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	13/02/2025		24	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	13/02/2025		8	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	13/02/2025		6	CHOCOLAT CHAUD	0,00 €	0,00 €			
JEAN-CHARLES ROUSSEAU	13/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CHRISTOPHE BOUE	13/02/2025	BRYO	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
SARAH VIGUIE	13/02/2025		1	LATTE	2,00 €	2,00 €			
IMENE THAMRI	13/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
UGO DE LUCA	13/02/2025		1	MOCACCINO	2,00 €	2,00 €			
CLARA MANHES	13/02/2025	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	13/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
SOLENE LAYBROS	13/02/2025		1	GRANOLA BOWL	4,50 €	4,50 €			
BARNABE LEVARD	14/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	14/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	14/02/2025	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
PIERRE HAMEL	14/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
NATHALIE GRENET	14/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	14/02/2025		5	CHOCOLAT CHAUD	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	14/02/2025		12	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	14/02/2025		7	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	14/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
ELODIE ALVES	17/02/2025		1	OURS BLANC	2,50 €	2,50 €			
BARNABE LEVARD	17/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MATTHIEU CROUZET	17/02/2025		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
LAETITIA MONTRICHARD	17/02/2025		2	DOUBLE ESPRESSO	0,50 €	1,00 €			
ANGELIQUE FOUIX	17/02/2025		1	FLAT WHITE	2,00 €	2,00 €			
VALERIE HAMEAU	17/02/2025		1	LATTE	2,50 €	2,50 €	SHOT SUP		
NATHALIE GRENET	17/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ELODIE ALVES	17/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €		DYLAN	
VALERIE HAMEAU	17/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L		
ANGELIQUE FOUIX	17/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
SUIVI CONSO GRATUITE	17/02/2025		10	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	17/02/2025		11	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	17/02/2025		5	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	17/02/2025		6	CHOCOLAT CHAUD	0,00 €	0,00 €			
MARGAUX DEROSIER	18/02/2025		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
BARNABE LEVARD	18/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VLAD CERISIER	18/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	18/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	18/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
JEAN CIAPA	18/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	18/02/2025		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
UGO DE LUCA	18/02/2025		1	MOCACCINO	2,50 €	2,50 €	L		
MELINE BOUYSSI	18/02/2025	METAVONICS	1	MOCACCINO	2,50 €	2,50 €	L		
MELODIE TYLER	18/02/2025		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
SUIVI CONSO GRATUITE	18/02/2025		20	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	18/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	18/02/2025		11	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	18/02/2025		3	CHOCOLAT CHAUD	0,00 €	0,00 €			
CHRISTELLE LAGAE	19/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MARGAUX DEROSIER	19/02/2025		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
MELINE BOUYSSI	19/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
AGUSTINA WEBER	19/02/2025		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
MATTHEW WALKER	19/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	19/02/2025		1	ESPRESSO TONIC	2,00 €	2,00 €			
ERIC GUIN	19/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MARION RATIER	19/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	19/02/2025		1	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	19/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	19/02/2025		1	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	19/02/2025		1	CHOCOLAT CHAUD	0,00 €	0,00 €			
SOLENE LAYBROS	19/02/2025		1	GRANOLA BOWL	4,50 €	4,50 €			
MATTHEW WALKER	20/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
CHRISTELLE LAGAE	20/02/2025	1000CAFES	1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MARGAUX DEROSIER	20/02/2025		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
BENOIT COUX	20/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ELSA CARDINAUD	20/02/2025		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
AGUSTINA WEBER	20/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ADRIEN MORQUE	20/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
SARAH VIGUIE	20/02/2025		1	LATTE	2,00 €	2,00 €			
ERIC GUIN	20/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	20/02/2025		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
MELINE BOUYSSI	20/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
ELSA CARDINAUD	20/02/2025		1	LATTE	2,00 €	2,00 €			
SARA TISSENIER	20/02/2025		1	LATTE	2,00 €	2,00 €			
GREGORY ESTRADE	20/02/2025		1	MOCACCINO	2,00 €	2,00 €			
CLAIRE BELLOC	20/02/2025	1000CAFES	1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
SUIVI CONSO GRATUITE	20/02/2025		4	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	20/02/2025		28	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	20/02/2025		18	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	20/02/2025		4	CHOCOLAT CHAUD	0,00 €	0,00 €			
VICTORIA PUYUELO	21/02/2025		2	CAPPUCCINO	1,50 €	3,00 €		(OFFERT) JOURNEE D\'ESSAI	
BENOIT COUX	21/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MELINE BOUYSSI	21/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
ADRIANA ROA	21/02/2025		2	CAPPUCCINO	1,50 €	3,00 €			
PIERRE HAMEL	21/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	21/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
CINDY HERAUD	21/02/2025		1	WHITE MATCHA LATTE	2,50 €	2,50 €			
BARNABE LEVARD	21/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
ARTHUR SUDRE	21/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
PAULINE SPINAZZE	21/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €			
MELINE BOUYSSI	21/02/2025		1	CHICOREE	0,50 €	0,50 €			
PASS CULTURE	21/02/2025		1	CAPPUCCINO	1,50 €	1,50 €		(OFFERT)PRIVAT. OUGANDA	
PASS CULTURE	21/02/2025		1	MOCACCINO	2,00 €	2,00 €		(OFFERT)PRIVAT. OUGANDA	
PASS CULTURE	21/02/2025		1	CHOCOLAT VIENNOIS	2,50 €	2,50 €		(OFFERT)PRIVAT. OUGANDA	
PASS CULTURE	21/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €		(OFFERT)PRIVAT. OUGANDA	
PASS CULTURE	21/02/2025		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €		(OFFERT)PRIVAT. OUGANDA	
SUIVI CONSO GRATUITE	21/02/2025		3	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	21/02/2025		13	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	21/02/2025		14	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	21/02/2025		12	CHOCOLAT CHAUD	0,00 €	0,00 €			
VALERIE HAMEAU	24/02/2025		2	LATTE	2,00 €	4,00 €			
BENOIT COUX	24/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
NATHALIE GRENET	24/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MELINE BOUYSSI	24/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
ANGELIQUE FOUIX	24/02/2025		1	FLAT WHITE	2,00 €	2,00 €			
BAPTISTE MATHUS	24/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CHRISTELLE LAGAE	24/02/2025	1000CAFES	2	DOUBLE ESPRESSO	0,50 €	1,00 €			
LEANE DOMERGUE	24/02/2025	CONTAKT	2	LATTE	2,00 €	4,00 €			
OUARDIA EL BONNOUHI	24/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
SUIVI CONSO GRATUITE	24/02/2025		4	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	24/02/2025		15	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	24/02/2025		11	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	24/02/2025		5	CHOCOLAT CHAUD	0,00 €	0,00 €			
MAX LEVER	24/02/2025		1	CHICOREE	0,50 €	0,50 €			
MATTHIEU CROUZET	25/02/2025		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
BENOIT COUX	25/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
BENOIT COUX	25/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CECILE BARTHES	25/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	25/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MELINE BOUYSSI	25/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
NATHALIE GRENET	25/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MARTIAL MONTRICHARD	25/02/2025		1	SCONE VEGAN	3,50 €	3,50 €			
VIRGINIE DEL RIEU	25/02/2025		1	CHAÏ AVOINE	2,50 €	2,50 €	L		
JEAN CIAPA	25/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	25/02/2025		1	SCONE VEGAN	3,50 €	3,50 €			
UGO DE LUCA	25/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L		
JEAN CIAPA	25/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	25/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
LEANE DOMERGUE	25/02/2025		1	LATTE	2,00 €	2,00 €			
ELODIE BOYER	25/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €		(offert) JOURNEE D\'ESSAI	
SUIVI CONSO GRATUITE	25/02/2025		3	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	25/02/2025		16	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	25/02/2025		13	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	25/02/2025		7	CHOCOLAT CHAUD	0,00 €	0,00 €			
BAPTISTE MATHUS	25/02/2025		1	SCONE VEGAN	3,50 €	3,50 €			
ANTHONY FELIN	26/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	26/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
BENOIT COUX	26/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
VICTORIA PUYUELO	26/02/2025		2	CAPPUCCINO	1,50 €	3,00 €			
JEAN CIAPA	26/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €			
UGO DE LUCA	26/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €			
MATTHEW WALKER	26/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	26/02/2025		1	MOCACCINO	2,50 €	2,50 €	L		
MELINE BOUYSSI	26/02/2025		1	SCONE VEGAN	3,50 €	3,50 €			
LEANE DOMERGUE	26/02/2025		2	LATTE	2,00 €	4,00 €			
PIERRE HAMEL	26/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
MICKAEL DE CONINCK	26/02/2025		1	CAPPUCCINO	1,50 €	1,50 €		lesydd@gmail.com	
SUIVI CONSO GRATUITE	26/02/2025		4	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	26/02/2025		18	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	26/02/2025		18	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	26/02/2025		1	CHOCOLAT CHAUD	0,00 €	0,00 €			
JEAN CIAPA	26/02/2025		1	MOCACCINO	2,50 €	2,50 €	L		
BENOIT COUX	26/02/2025		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
SARA TISSENIER	26/02/2025		1	LATTE	2,00 €	2,00 €			
BARNABE LEVARD	27/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
ANTHONY FELIN	27/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
VLAD CERISIER	27/02/2025		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	27/02/2025	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
JEAN CIAPA	27/02/2025		1	CHICORYCCINO	2,00 €	2,00 €	L		
ERIC GUIN	27/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
SARAH VIGUIE	27/02/2025		1	LATTE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	27/02/2025		1	CHAÏ AVOINE	2,00 €	2,00 €			
UGO DE LUCA	27/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
ERIC GUIN	27/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
LEANE DOMERGUE	27/02/2025	CONTAKT	2	LATTE	2,00 €	4,00 €			
BAPTISTE MATHUS	27/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
GREGOIRE CORBIERE	27/02/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €		LAURENT	
BENJAMIN MERIEAU	27/02/2025		1	FLAT WHITE	2,00 €	2,00 €			
LUCILE BAILLOU	27/02/2025		1	CAPPUCCINO	1,50 €	1,50 €		lucile.baillou@elastic.co	
MANON OLIVIER	27/02/2025		1	OURS BLANC	2,50 €	2,50 €			
SUIVI CONSO GRATUITE	27/02/2025		2	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	27/02/2025		17	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	27/02/2025		14	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	27/02/2025		4	CHOCOLAT GLACE	0,00 €	0,00 €			
LAURA ARLES	27/02/2025		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
ADRIANA ROA	28/02/2025		2	CAPPUCCINO	1,50 €	3,00 €			
MATTHIEU CROUZET	28/02/2025		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
PIERRE HAMEL	28/02/2025		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
VALENTIN RENAUD	28/02/2025		1	MOCACCINO	2,50 €	2,50 €	L		
MELINE BOUYSSI	28/02/2025		1	CHICORYCCINO	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	28/02/2025		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BARNABE LEVARD	28/02/2025		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
NATHALIE GRENET	28/02/2025		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
BAPTISTE MATHUS	28/02/2025		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
SUIVI CONSO GRATUITE	28/02/2025		1	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	28/02/2025		12	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	28/02/2025		8	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	28/02/2025		4	CHOCOLAT CHAUD	0,00 €	0,00 €			
VALERIE HAMEAU	03/03/2025		2	LATTE	2,00 €	4,00 €			
GREGORY ESTRADE	03/03/2025		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ANGELIQUE FOUIX	03/03/2025		1	FLAT WHITE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	03/03/2025	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
MELODIE TYLER	03/03/2025		1	GRANOLA BOWL	4,50 €	4,50 €			
SUIVI CONSO GRATUITE	03/03/2025		3	boisson de la semaine	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/03/2025		12	allongé	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/03/2025		12	espresso	0,00 €	0,00 €			
SUIVI CONSO GRATUITE	03/03/2025		12	CHOCOLAT CHAUD	0,00 €	0,00 €			
VALERIE HAMEAU	03/03/2025		1	CHICORYCCINO	1,50 €	1,50 €			';
    }

}
