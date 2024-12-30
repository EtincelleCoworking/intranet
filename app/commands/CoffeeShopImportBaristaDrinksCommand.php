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
                return 4299;
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
            case 'MELANIE BESSAGNET': return 990;
            case 'FABRICE RAKOTONARIVO': return 5277;
            case 'CLARISSE LOU': return 7096;
            case 'LOUIS JACQUES': return 7095;
            //case 'LOUIS GOUEZE': return null;
            case 'LESLIE ROUZIER': return 7078;
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
            case 'BENJAMIN GUILHEMJOUAN': return 5569;
            case 'COLLEEN HANRIOT': return 6017;
            case 'BENOIT EL AMRANI': return 4301;
            case 'JEAN-PHILIPPE KHA': return 4314;
            case 'MARION RATIER': return 7041;
            case 'LEANE DOMERGUE': return 7099;
            case 'HELENE BERRIER': return 7125;
            case 'CATHERINE DORE': return 6575;
            case 'GREGORY ESTRADE': return 145;
            case 'CORALINE RASSET': return 5680;
            case 'BAPTISTE VILLENEUVE': return 7202;
            case 'ANAIS EL AOUD': return 6195;
            case 'SAMANTHA HARRACA': return 5759;
            //case 'EFIARETA EHAMELO': return null;
            case 'ESTEVE PINYOL': return 2571;
            case 'JEAN-MARC D\'ANDRIA': return 745;
            case 'MORGAN URIEN': return 6697;
            case 'LEO VINCENT': return 6077;
            case 'GERALD GOUNOT': return 7134;
            case 'AMELIE BIMONT': return 6826;
            case 'CHERIF MILI': return 2993;
            case 'ROBERTO PASQUA': return 6386;
            case 'EDDINE SAIDI': return 7101;
            case 'ALIA DOYEN': return 7180;
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
        return 'BAPTISTE VILLENEUVE	07/10/2024	METAVONICS	1	SNEAKERS*	1,00 €	1,00 €		Offert SH	
LOUIS GOUEZE	30/10/2024	METAVONICS	1	CHOCOLAT VIENNOIS	2,00 €	2,00 €		Offert SH	
CHANTAL PERDIGAU	04/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHEW WALKER	04/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MAX LEVER	04/11/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	PIMENT		
MELINE BOUYSSI	04/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
MELODIE TYLER	04/11/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	PIMENT		
VALERIE ALASLUQUETAS	04/11/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
ARIADNA MATAS	05/11/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
BAPTISTE MATHUS	05/11/2024		1	MOCACCINO	2,00 €	2,00 €			
BENJAMIN GUILHEMJOUAN	05/11/2024		1	IMMUNITY SHOT	1,00 €	1,00 €			
CAROLYNE CASSE	05/11/2024	ALMA	1	CAPPUCCINO AVOINE	1,50 €	1,50 €		Offert SH	
COLLEEN HANRIOT	05/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
CORALINE RASSET	05/11/2024		4	IMMUNITY SHOT	1,00 €	4,00 €			
GREGOIRE CORBIERE	05/11/2024		1	IMMUNITY SHOT	1,00 €	1,00 €			
JEAN CIAPA	05/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
JULIEN SACCAREAU	05/11/2024	ALMA	1	CAPPUCCINO AVOINE	1,50 €	1,50 €		Offert SH	
JULIEN SACCAREAU	05/11/2024	ALMA	2	DOUBLE ESPRESSO	0,50 €	1,00 €		Offert SH	
MELINE BOUYSSI	05/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
MELODIE TYLER	05/11/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
PAULINE SPINAZZE	05/11/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
PAULINE SPINAZZE	05/11/2024		1	IMMUNITY SHOT	1,00 €	1,00 €			
SEBASTIEN FLOCHLAY	05/11/2024		1	IMMUNITY SHOT	1,00 €	1,00 €			
SEBASTIEN FLOCHLAY	05/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
VALERIE ALASLUQUETAS	05/11/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VIRGINIE DEL RIEU	05/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
VIRGINIE DEL RIEU	05/11/2024		1	CHAÏ AVOINE	3,00 €	3,00 €	XL		
CASSANDRA BONNAFOUS	06/11/2024		1	IMMUNITY SHOT	1,00 €	1,00 €			
BENOIT COUX	07/11/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
BENOIT EL AMRANI	07/11/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
ELENA PERROUIN	07/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MAELIA LEGRAND	07/11/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
MATTHIEU CROUZET	07/11/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
MELINE BOUYSSI	07/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	08/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	08/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	08/11/2024		1	MOCACCINO	2,00 €	2,00 €			
FABRICE LUCAS	08/11/2024	METAVONICS	1	CAPPUCCINO	1,50 €	1,50 €			
GREGORY ESTRADE	08/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MELINE BOUYSSI	08/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
ANASTASIA DE SANTIS	12/11/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
BAPTISTE MATHUS	12/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CATHERINE DORE	12/11/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
CLAIRE BELLOC	12/11/2024	1000CAFES	2	MACCHIATO/NOISETTE	1,00 €	2,00 €			
CLARA MANHES	12/11/2024	YUKAN	1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
ELENA PERROUIN	12/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
EMMANUELLE VAN DEN STEEN	12/11/2024		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
HELENE BERRIER	12/11/2024		1	CHAÏ AVOINE	2,50 €	2,50 €	L	helene.berrier@groupe-sos.org	
JEAN CIAPA	12/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
LAURE SARDELLA	12/11/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LEANE DOMERGUE	12/11/2024	CONTAKT	1	LATTE	2,00 €	2,00 €		leane.domergue@contakt.eco	
MAELIA LEGRAND	12/11/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
MAELIA LEGRAND	12/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
MARGAUX DEROSIER	12/11/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
MELINE BOUYSSI	12/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
NATHALIE GRENET	12/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
SOLENE ROSSARD	12/11/2024		1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	12/11/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
UGO DE LUCA	12/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
VIRGINIE DEL RIEU	12/11/2024		2	CHAÏ AVOINE	2,00 €	4,00 €			
ANGELIQUE FOUIX	13/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CLARA MANHES	13/11/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
IRIS BORRUT	13/11/2024		1	LATTE	2,00 €	2,00 €			
IRIS BORRUT	13/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	13/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
JEAN CIAPA	13/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	13/11/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LEANE DOMERGUE	13/11/2024	CONTAKT	1	LATTE	2,00 €	2,00 €			
LUCIE SCHAMING	13/11/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
MAELIA LEGRAND	13/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
MAELIA LEGRAND	13/11/2024		1	MATCHA LATTE AVOINE	2,50 €	2,50 €	L		
MARION RATIER	13/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MELINE BOUYSSI	13/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
MELODIE TYLER	13/11/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
OUARDIA EL BONNOUHI	13/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	13/11/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
PAULINE SARDA	13/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
VIRGINIE DEL RIEU	13/11/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
BAPTISTE MATHUS	14/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
EINAT ARGON	14/11/2024	FILIGRAN	1	CAPPUCCINO	1,50 €	1,50 €			
EINAT ARGON	14/11/2024	FILIGRAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	14/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ERIC GUIN	14/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	14/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN-PHILIPPE KHA	14/11/2024	FILIGRAN	1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JERMAIN NJEMANZE	14/11/2024	FILIGRAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
JERMAIN NJEMANZE	14/11/2024	FILIGRAN	1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
LAETITIA RUAULT DURAND	14/11/2024		1	MOCACCINO	2,00 €	2,00 €			
MELINE BOUYSSI	14/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	14/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
SANCIE VANNEAUD	14/11/2024	FILIGRAN	2	LATTE	2,00 €	4,00 €			
SARAH VIGUIE	14/11/2024		1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	14/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
VALERIE ALASLUQUETAS	14/11/2024	YUKAN	1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
VALERIE ALASLUQUETAS	14/11/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	14/11/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
ADRIEN MORQUE	15/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	15/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	15/11/2024		1	MOCACCINO	2,00 €	2,00 €			
BAPTISTE VILLENEUVE	15/11/2024	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
CHRISTELLE LAGAE	15/11/2024	1000CAFES	1	DOUBLE ESPRESSO	0,50 €	0,50 €			
CLAIRE BELLOC	15/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
CLARA MANHES	15/11/2024	YUKAN	1	MOCACCINO	2,00 €	2,00 €			
EMMANUELLE BIADI-COMET	15/11/2024		1	CHICOREE	0,50 €	0,50 €			
MELINE BOUYSSI	15/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
PAULINE SARDA	15/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
PAULINE SARDA	15/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PIERRE HAMEL	15/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
VALERIE ALASLUQUETAS	15/11/2024	YUKAN	1	CHAÏ LATTE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	15/11/2024	YUKAN	1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
BARNABE LEVARD	18/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	18/11/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ELENA PERROUIN	18/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
GREGORY ESTRADE	18/11/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
LEANE DOMERGUE	18/11/2024	CONTAKT	1	LATTE	2,00 €	2,00 €			
ANAIS EL AOUD	19/11/2024	SPARK	1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
ELENA PERROUIN	19/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ELENA PERROUIN	19/11/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
GLORIA FRADIN	19/11/2024	SPARK	1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
LAURE SARDELLA	19/11/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LAURE SARDELLA	19/11/2024	TECHNIA	1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
LUCIE SCHAMING	19/11/2024		1	LATTE	2,00 €	2,00 €			
MAELIA LEGRAND	19/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
MARGAUX DEROSIER	19/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
OUARDIA EL BONNOUHI	19/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ERIC GUIN	20/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
FRANCOIS HELLOCO	20/11/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
LEANE DOMERGUE	20/11/2024	CONTAKT	1	LATTE	2,00 €	2,00 €			
MELINE BOUYSSI	20/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
SOFIE LEON	20/11/2024		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
CHRISTOPHE BOUE	21/11/2024	BRYO	1	LATTE MACCHIATO	2,50 €	2,50 €			
JOCHEN GRUNBECK	21/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
LEANE DOMERGUE	21/11/2024	CONTAKT	1	LATTE	2,00 €	2,00 €			
MAELIA LEGRAND	21/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
MARGAUX DEROSIER	21/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
MELINE BOUYSSI	21/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
SARAH VIGUIE	21/11/2024		1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	21/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	22/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
GLORIA FRADIN	22/11/2024	SPARK	1	CHAÏ LATTE	2,00 €	2,00 €			
MELINE BOUYSSI	22/11/2024	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
SAMANTHA HARRACA	22/11/2024	LE BON COIN	1	THE MATCHA	2,00 €	2,00 €			
BAPTISTE VILLENEUVE	22/11/2024	METAVONICS	1	KIT KAT*	1,00 €	1,00 €		Offert SH	
FRANCOIS HELLOCO	22/11/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
MATTHEW WALKER	25/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	25/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
AGUSTINA WEBER	25/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MELINE BOUYSSI	25/11/2024	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €			
ANGELIQUE FOUIX	25/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
JULIE COUSSE	25/11/2024		1	MOCACCINO	2,00 €	2,00 €			
ALEXANDRE BRUN	25/11/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
MAELIA LEGRAND	25/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
BAPTISTE MATHUS	26/11/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
AGUSTINA WEBER	26/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MAELIA LEGRAND	26/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
ANAEL MEGNA	26/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	26/11/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
ELENA PERROUIN	26/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MATTHIEU CROUZET	27/11/2024		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
MARION RATIER	27/11/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
SOLENE ROSSARD	27/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VALERIE ALASLUQUETAS	27/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MAELIA LEGRAND	27/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
MELINE BOUYSSI	27/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
ERIC GUIN	27/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	27/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ANGELIQUE FOUIX	27/11/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	27/11/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
FADEL DIENE	27/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
KRISTELL THOMAS	27/11/2024		1	MOCA	1,00 €	1,00 €			
EFIARETA EHAMELO	27/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €		Offert SH	efiareta@gmail.com
BAPTISTE MATHUS	28/11/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
SOPHIE JAMAIN	28/11/2024		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
CHRISTELLE LAGAE	28/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
CLAIRE BELLOC	28/11/2024		1	LATTE MACCHIATO	2,50 €	2,50 €			
SARAH VIGUIE	28/11/2024		1	LATTE MACCHIATO	2,50 €	2,50 €			
LAETITIA RUAULT DURAND	28/11/2024		1	MOCACCINO	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	28/11/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
ERIC GUIN	28/11/2024		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
UGO DE LUCA	28/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
MAELIA LEGRAND	28/11/2024		1	CHICORYCCINO	2,00 €	2,00 €	L		
SOPHIE JAMAIN	28/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
SOPHIE JAMAIN	28/11/2024		1	CAPPUCCINO GLACE	2,00 €	2,00 €		ANNA VIROT	
JEAN CIAPA	28/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
MARION RATIER	29/11/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
GLORIA FRADIN	29/11/2024		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
MELINE BOUYSSI	29/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
CLAIRE BELLOC	29/11/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €			
CLAIRE BELLOC	29/11/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
BARNABE LEVARD	29/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MAELIA LEGRAND	29/11/2024		1	CHICORYCCINO	1,50 €	1,50 €			
CLAIRE BELLOC	29/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ESTEVE PINYOL	29/11/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN-MARC D\'ANDRIA	29/11/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €
BAPTISTE VILLENEUVE	29/11/2024		1	KIT KAT*	1,00 €	1,00 €		Offert SH
MAELIA LEGRAND	02/12/2024		3	CHICORYCCINO	2,00 €	6,00 €	L
MELODIE TYLER	02/12/2024		1	GRANOLA BOWL	4,50 €	4,50 €
LEANE DOMERGUE	02/12/2024		1	LATTE	2,00 €	2,00 €
CLAIRE BELLOC	02/12/2024		1	LATTE	2,00 €	2,00 €
CLARA MANHES	02/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
NATHALIE GRENET	02/12/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €
AMINE EL AZIZI	02/12/2024		1	FLAT WHITE	2,00 €	2,00 €
VALERIE HAMEAU	02/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
AURELIE PICHOT	02/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €
ELENA PERROUIN	03/12/2024		1	GRANOLA BOWL	4,50 €	4,50 €
AMINE EL AZIZI	03/12/2024		1	FLAT WHITE	2,00 €	2,00 €
BAPTISTE MATHUS	03/12/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €
MARGAUX DEROSIER	03/12/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €
LAURE SARDELLA	03/12/2024		1	ESPRESSO TONIC	2,00 €	2,00 €
MAELIA LEGRAND	03/12/2024		2	CHICORYCCINO	2,00 €	4,00 €	L
LEANE DOMERGUE	03/12/2024		1	LATTE	2,00 €	2,00 €
CEDRIC BOUCHE	03/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
BAPTISTE MATHUS	03/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
CLARA MANHES	03/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
AGUSTINA WEBER	03/12/2024		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €
FRANCOIS HELLOCO	03/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €
ALEXANDRE BRUN	03/12/2024		1	GRANOLA BOWL	4,50 €	4,50 €
BAPTISTE VILLENEUVE	03/12/2024		1	KIT KAT*	1,00 €	1,00 €		Offert SH
ROBERTO PASQUA	03/12/2024		1	LATTE MACCHIATO	2,50 €	2,50 €
MAELIA LEGRAND	03/12/2024		1	CHICORYCCINO	2,00 €	2,00 €	L
ERIC GUIN	04/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
MATTHIEU CROUZET	04/12/2024		1	MOCACCINO	2,50 €	2,50 €	L
ELENA PERROUIN	04/12/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €
CLARA MANHES	04/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
MAELIA LEGRAND	04/12/2024		1	CHICORYCCINO	2,00 €	2,00 €	L
CHERIF MILI	04/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
BAPTISTE MATHUS	05/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
BENOIT COUX	05/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
ELENA PERROUIN	05/12/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €
MARGAUX DEROSIER	05/12/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €
CHRISTOPHE BOUE	05/12/2024	BRYO	2	LATTE MACCHIATO	2,50 €	5,00 €
VIRGINIE DEL RIEU	05/12/2024		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL
LAETITIA RUAULT DURAND	05/12/2024		1	MOCACCINO	2,00 €	2,00 €
VIRGINIE DEL RIEU	05/12/2024		1	CHAÏ AVOINE	3,00 €	3,00 €	XL
MELINE BOUYSSI	05/12/2024	METAVONICS	1	CHICOREE	0,50 €	0,50 €
NATHALIE GRENET	05/12/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €
CHRISTOPHE BOUE	05/12/2024	BRYO	1	MACCHIATO/NOISETTE	1,00 €	1,00 €
AMINE EL AZIZI	05/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
MARION RATIER	06/12/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €
BAPTISTE MATHUS	06/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
ALEXANDRE BRUN	06/12/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €		(VALENTINE)
CLARA MANHES	06/12/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €
ALEXANDRE BRUN	06/12/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €
VALERIE ALASLUQUETAS	06/12/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €
MAEL VALAIS	09/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
MAEL VALAIS	09/12/2024		1	CHICOREE	0,50 €	0,50 €
ANAIS EL AOUD	09/12/2024	SPARK	1	PUMPKIN SPICE LATTE	3,00 €	3,00 €
MELINE BOUYSSI	09/12/2024	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €
VALERIE ALASLUQUETAS	09/12/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €
PHILIPPE LANDES	09/12/2024		2	DOUBLE ESPRESSO	0,50 €	1,00 €
PHILIPPE LANDES	09/12/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €
OUARDIA EL BONNOUHI	09/12/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €
AMELIE BIMONT	09/12/2024		1	GRANOLA BOWL	4,50 €	4,50 €
LAURE SARDELLA	10/12/2024		1	ESPRESSO TONIC	2,00 €	2,00 €
MOUAD BELGHITI	10/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
BENOIT COUX	10/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
GREGOIRE CORBIERE	10/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
CATHERINE DORE	10/12/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €
LEANE DOMERGUE	10/12/2024	CONTAKT	2	LATTE	2,00 €	4,00 €
BAPTISTE MATHUS	10/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
VIRGINIE DEL RIEU	10/12/2024		1	CHAÏ AVOINE	2,50 €	2,50 €	L
OUARDIA EL BONNOUHI	10/12/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €
BAPTISTE VILLENEUVE	10/12/2024	METAVONICS	1	TWIX*	1,00 €	1,00 €		Offert SH
BARNABE LEVARD	10/12/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €
BAPTISTE MATHUS	10/12/2024		1	MOCACCINO	2,00 €	2,00 €
VALERIE ALASLUQUETAS	10/12/2024	YUKAN	2	CAPPUCCINO	1,50 €	3,00 €
HELENE FABRE	10/12/2024		1	THE MATCHA	2,00 €	2,00 €
ESTEVE PINYOL	11/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
PAULINE SARDA	11/12/2024		1	MOCACCINO	2,00 €	2,00 €
SOLENE ROSSARD	11/12/2024	TECHNIA	1	LATTE	2,00 €	2,00 €
SOFIE LEON	11/12/2024		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €
LEANE DOMERGUE	11/12/2024	CONTAKT	1	LATTE	2,00 €	2,00 €
VALERIE ALASLUQUETAS	11/12/2024	YUKAN	1	MACCHIATO/NOISETTE AVOINE	0,50 €	0,50 €
CHERIF MILI	11/12/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €
MARYLENE LAURENT	11/12/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €
GERALD GOUNOT	11/12/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €
ADRIEN MORQUE	12/12/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €
SARAH VIGUIE	12/12/2024		1	LATTE	2,00 €	2,00 €
MARGAUX DEROSIER	12/12/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €
ELENA PERROUIN	12/12/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €
MELINE BOUYSSI	12/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
VIRGINIE DEL RIEU	12/12/2024		1	CHAÏ AVOINE	2,00 €	2,00 €
LAETITIA RUAULT DURAND	12/12/2024		2	MOCACCINO	2,00 €	4,00 €
UGO DE LUCA	12/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
VALERIE ALASLUQUETAS	12/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
NATHALIE GRENET	12/12/2024		2	MACCHIATO/NOISETTE	1,00 €	2,00 €
VIRGINIE DEL RIEU	12/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
JEAN CIAPA	12/12/2024		1	CHICORYCCINO	1,50 €	1,50 €
ANAEL MEGNA	12/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
MAX LEVER	12/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €
BAPTISTE MATHUS	12/12/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €
CASSANDRA BONNAFOUS	12/12/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €
LEO VINCENT	12/12/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €
ADRIEN MORQUE	13/12/2024		1	CAPPUCCINO	1,50 €	1,50 €
MORGAN URIEN	13/12/2024	BLABLACAR	1	CAPPUCCINO	1,50 €	1,50 €
MELINE BOUYSSI	13/12/2024	METAVONICS	1	CHICORYCCINO	1,50 €	1,50 €
ESTEVE PINYOL	13/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €
MANU DEJEAN	13/12/2024		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €		offert??
MANU DEJEAN	13/12/2024		1	PUMPKIN SPICE LATTE	3,00 €	3,00 €		(ISABELLE)
ALEXANDRE BRUN	13/12/2024		1	CAPPUCCINO	1,50 €	1,50 €		(VALENTINE)	
ALIA DOYEN	13/12/2024	BLABLACAR	1	CHICOREE	0,50 €	0,50 €			
MATTHIEU CROUZET	13/12/2024		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
ESTEVE PINYOL	13/12/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	13/12/2024		1	AMERICANO DOUBLE SHOT	1,00 €	1,00 €	1SHOT SUPP		
EDDINE SAIDI	13/12/2024	SPARK	1	FLAT WHITE	2,00 €	2,00 €			
ANAIS EL AOUD	13/12/2024	SPARK	1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
FRANCOIS HELLOCO	13/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €			
ALEXANDRE BRUN	13/12/2024		1	INFUSION GINGER LEMON	0,50 €	0,50 €		(VALENTINE)	';
    }

}
