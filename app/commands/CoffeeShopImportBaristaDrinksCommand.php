<?php

use Illuminate\Console\Command;

// ajouter le commentaire sur le détail des boissons

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
                            }
                            //  return false;
                        }
                    }
                    $quantity = $tokens[self::FIELD_QUANTITY];
                    $occurs_at = preg_replace('|^([0-9]{2})/([0-9]{2})/([0-9]{4})$|', '$3-$2-$1', $tokens[self::FIELD_DATE]);
                    $product = \Illuminate\Support\Str::slug($tokens[self::FIELD_PRODUCT]);

                    $product_price = $this->getProductPricing($product);
                    if (false === $product_price) {
                        $product_price = 0;
                        $this->output->writeln(sprintf("<error>Produit inconnu : [%s]</error>", $product));
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
                        }else{
                            if($addons[$addon] == $addon_price){
                                // ok
                            }else{
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
                    $order->product_addon_price = $addon_price;
                    $order->product_addon_comment = $addon_comment;
                    //$order->save();
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
        return array();
    }

    private function getUserId($name)
    {
        switch ($name) {
            case 'AMINE ELAZIZI':
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
                return 5263;
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
            case 'SARAH VIGUIE': return 7073;
            case 'JULIEN CARVAJAL': return 7075;
            case 'CAROLINE TERGEMINA': return 7074;
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
        return 'AMINE EL AZIZI	17/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	17/06/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CEDRIC BOUCHE	17/06/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MELINE BOUYSSI	17/06/2024		1	MOCACCINO AVOINE	2,00 €	2,00 €			
NATHALIE GRENET	17/06/2024		1	MACCHIATO/NOISETTE AVOINE	1,00 €	1,00 €			
VALENTIN RENAUD	17/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	18/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AGUSTINA WEBER	18/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ALEXANDRE BRUN	18/06/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
BARNABE LEVARD	18/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CEDRIC BOUCHE	18/06/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MARIE-LAURE MOENS	18/06/2024		1	LATTE	2,00 €	2,00 €			
MATTHIEU CROUZET	18/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
NATHALIE GRENET	18/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
UGO DE LUCA	18/06/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	18/06/2024		2	CHAÏ AVOINE	2,00 €	4,00 €			
ADRIANA ROA	19/06/2024		2	CAPPUCCINO	1,50 €	3,00 €			
AMINE EL AZIZI	19/06/2024		1	FLAT WHITE	2,00 €	2,00 €			
CEDRIC BOUCHE	19/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	19/06/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
ADRIANA ROA	20/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CEDRIC BOUCHE	20/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
NATHALIE GRENET	20/06/2024		1	MACCHIATO/NOISETTE AVOINE	1,00 €	1,00 €			
PAULINE SPINAZZE	20/06/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
SARAH VIGUIE	20/06/2024		1	LATTE	2,00 €	2,00 €		Offert SH	
VIRGINIE DEL RIEU	20/06/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
ADRIEN MORQUE	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CEDRIC BOUCHE	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JOCHEN GRUNBECK	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MARIE-LAURE MOENS	21/06/2024		1	FLAT WHITE	2,00 €	2,00 €			
MATTHEW WALKER	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	21/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VLAD CERISIER	21/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ANGELIQUE FOUIX	24/06/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
ANNE-LISE H.	24/06/2024	LEXOM	1	LATTE	2,00 €	2,00 €		Offert SH	
CAMILLE C.	24/06/2024	LEXOM	1	LATTE	2,00 €	2,00 €		Offert SH	
CELINE FABRE	24/06/2024		1	CAPPUCCINO	1,50 €	1,50 €		Offert SH	
FABRICE LUCAS	24/06/2024		1	MOON MILK	2,00 €	2,00 €			
JEAN CIAPA	24/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	24/06/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
MELINE BOUYSSI	24/06/2024		1	MOON MILK	2,00 €	2,00 €			
PHILIPPE LANDES	24/06/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
VALERIE HAMEAU	24/06/2024		1	MOON MILK	2,00 €	2,00 €			
ANASTASIA DE SANTIS	25/06/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
ANNE-LISE H.	25/06/2024	LEXOM	1	LATTE	2,00 €	2,00 €		Offert SH	
ANNE-LISE H.	25/06/2024	LEXOM	1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €		Offert SH	
BAPTISTE MATHUS	25/06/2024		1	MOCACCINO	2,00 €	2,00 €			
BENOIT COUX	25/06/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CAMILLE C.	25/06/2024	LEXOM	1	LATTE	2,00 €	2,00 €		Offert SH	
JEAN CIAPA	25/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
JEAN CIAPA	25/06/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
JEAN CIAPA	25/06/2024		1	MOON MILK	2,00 €	2,00 €			
LAETITIA RUAULT DURAND	25/06/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
MARGAUX DEROSIER	25/06/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
MAXIME LEVEAU	25/06/2024		3	DOUBLE ESPRESSO	0,50 €	1,50 €			
NATHALIE GRENET	25/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	25/06/2024		1	MOCACCINO	2,00 €	2,00 €			
UGO DE LUCA	25/06/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
AGUSTINA WEBER	27/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	27/06/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
BLANDINE DE CHALLEMAISON	27/06/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
CEDRIC BOUCHE	27/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	27/06/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
JOCHEN GRUNBECK	27/06/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
KRISTELL THOMAS	27/06/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
LUCILLE BISBAU	27/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
NATHALIE GRENET	27/06/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PHILIPPE LANDES	27/06/2024		2	LATTE GLACE (NOISETTE)	2,50 €	5,00 €			
UGO DE LUCA	27/06/2024		1	LATTE GLACE	2,50 €	2,50 €			
VIRGINIE DEL RIEU	27/06/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	27/06/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
AYMERIC JOUON	28/06/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
BAPTISTE MATHUS	28/06/2024		1	MOCACCINO	2,00 €	2,00 €			
JOHAN RITTERSHAUS	28/06/2024		1	CAFE FRAPPE	2,00 €	2,00 €	1 SHOT		
NATHALIE GRENET	28/06/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CEDRIC BOUCHE	01/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
KEVIN LEMELE	01/07/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MELINE BOUYSSI	01/07/2024		1	MOCACCINO	2,00 €	2,00 €			
MOUAD BELGHITI	01/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	02/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	02/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BARNABE LEVARD	02/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	02/07/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JACQUES DUTILLEUX	02/07/2024		1	MOON MILK	2,00 €	2,00 €			
JEAN CIAPA	02/07/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
JEAN CIAPA	02/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
MELINE BOUYSSI	02/07/2024		1	MOON MILK	2,00 €	2,00 €			
MOUAD BELGHITI	02/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	02/07/2024		1	MOCACCINO	2,00 €	2,00 €			
PAULINE SARDA	02/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	02/07/2024		1	MOCACCINO	2,00 €	2,00 €			
PHILIPPE LANDES	02/07/2024		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €	XL		
PIERRE HAMEL	02/07/2024		1	MOON MILK	2,00 €	2,00 €			
UGO DE LUCA	02/07/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
UGO DE LUCA	02/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
VALENTIN RENAUD	02/07/2024		1	LATTE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	02/07/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	02/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
ADRIANA ROA	03/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIEN BEUDIN	03/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
ADRIEN BEUDIN	03/07/2024		1	CHAÏ GLACE	2,50 €	2,50 €			
BARNABE LEVARD	03/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	03/07/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MARGAUX DEROSIER	03/07/2024		1	MOCACCINO (CARAMEL)	2,50 €	2,50 €	XL		
MELINE BOUYSSI	03/07/2024		1	MOCACCINO	2,00 €	2,00 €			
PHILIPPE LANDES	03/07/2024		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €			
PIERRE DE DUFOURCQ	03/07/2024		1	GENMAÏCHA	2,00 €	2,00 €
        ?	04/07/2024	RESILIENCE	2	CAPPUCCINO	1,50 €	3,00 €		Offert SH
        ?	04/07/2024	RESILIENCE	1	CAPPUCCINO AVOINE	1,50 €	1,50 €		Offert SH
        ?	04/07/2024	RESILIENCE	1	CHAÏ LATTE	2,00 €	2,00 €		Offert SH	
ADRIANA ROA	04/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
EINAT ARGON	04/07/2024	FILIGRAN	1	CAPPUCCINO	1,50 €	1,50 €		Offert SH	
JEAN CIAPA	04/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
JERMAIN NJEMANZE	04/07/2024	FILIGRAN	1	MOCACCINO	2,00 €	2,00 €		Offert SH	
MARGAUX DEROSIER	04/07/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
MARTIAL MONTRICHARD	04/07/2024	METAVONICS	1	GENMAÏCHA	2,00 €	2,00 €			
MATTHEW WALKER	04/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	04/07/2024		1	MOCACCINO	2,00 €	2,00 €			
MELINE BOUYSSI	04/07/2024		1	MOCACCINO AVOINE	2,00 €	2,00 €			
OLIVIER REVIAL	04/07/2024		1	GENMAÏCHA	2,00 €	2,00 €			
PAULINE SARDA	04/07/2024		1	MOCACCINO	2,00 €	2,00 €			
SANCIE VANNEAUD	04/07/2024	FILIGRAN	1	LATTE	2,00 €	2,00 €		Offert SH	
ALEXANDRE BRUN	05/07/2024	EMERTON	2	LATTE	2,00 €	4,00 €		Offert SH	
BENJAMIN CORRET	05/07/2024		1	GENMAÏCHA	2,00 €	2,00 €			
HELENE FABRE	05/07/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MATTHIEU CROUZET	05/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	08/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AGUSTINA WEBER	08/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ANGELIQUE FOUIX	08/07/2024		1	AEROCANO	1,50 €	1,50 €			
CEDRIC BOUCHE	08/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	08/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
MATTHEW WALKER	08/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
NATHALIE GRENET	08/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	09/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ANASTASIA DE SANTIS	09/07/2024		1	CHAÏ GLACE	2,50 €	2,50 €			
ARNAUD THOMAS-SERVAIS	09/07/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	09/07/2024		1	AEROCANO	1,50 €	1,50 €			
CEDRIC BOUCHE	09/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
GREGOIRE CORBIERE	09/07/2024		1	AEROCANO	1,50 €	1,50 €			
JEAN CIAPA	09/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
LAETITIA RUAULT DURAND	09/07/2024		1	AEROCANO	1,50 €	1,50 €			
LILA RAMALINGOM	09/07/2024		2	LATTE GLACE	2,50 €	5,00 €			
PHILIPPE LANDES	09/07/2024		1	LATTE GLACE (VANILLE)	3,00 €	3,00 €	XL		
UGO DE LUCA	09/07/2024		1	AEROCANO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	09/07/2024		1	CHAÏ GLACE	2,50 €	2,50 €			
ADRIANA ROA	10/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	10/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AGUSTINA WEBER	10/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ALEXIS BASSET	10/07/2024		1	MOCACCINO	2,00 €	2,00 €			
ANGELIQUE FOUIX	10/07/2024		1	AEROCANO	1,50 €	1,50 €			
BENJAMIN BOUVET	10/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CEDRIC BOUCHE	10/07/2024		1	AEROCANO	1,50 €	1,50 €			
CEDRIC BOUCHE	10/07/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
EMMANUELLE VAN DEN STEEN	10/07/2024		2	AEROCANO	1,50 €	3,00 €			
JACQUES DUTILLEUX	10/07/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
JORDAN LLIDO	10/07/2024		1	AEROCANO	1,50 €	1,50 €			
LAURE SARDELLA	10/07/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LUCIE SCHAMING	10/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
MELINE BOUYSSI	10/07/2024		1	MOCACCINO AVOINE	2,00 €	2,00 €			
PAULINE SARDA	10/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ARNAUD THOMAS-SERVAIS	11/07/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	11/07/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	11/07/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CEDRIC BOUCHE	11/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	11/07/2024		1	AEROCANO	1,50 €	1,50 €			
JEAN CIAPA	11/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
LAETITIA RUAULT DURAND	11/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
MARGAUX DEROSIER	11/07/2024		1	LATTE MACCHIATO	3,50 €	3,50 €	DOUBLE SHOT + XL		
MELINE BOUYSSI	11/07/2024		1	CHOCOLAT GLACE	1,50 €	1,50 €			
SOHAIR	11/07/2024	METAVONICS	1	CHAÏ GLACE	2,50 €	2,50 €		Offert SH	
UGO DE LUCA	11/07/2024		1	AEROCANO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	11/07/2024		1	CHAÏ GLACE	2,50 €	2,50 €			
VIRGINIE DEL RIEU	11/07/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
BENOIT COUX	12/07/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
MATTHIEU CROUZET	12/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MELINE BOUYSSI	12/07/2024		1	MOCACCINO AVOINE	2,00 €	2,00 €			
OUARDIA EL BONNOUHI	12/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AMEL GUEDRI	15/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	15/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
MELINE BOUYSSI	15/07/2024		1	MOCACCINO AVOINE	2,00 €	2,00 €			
OUARDIA EL BONNOUHI	15/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	16/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	16/07/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
LAETITIA RUAULT DURAND	16/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
MARIE-LAURE MOENS	16/07/2024		1	BOUNTY*	1,00 €	1,00 €		Offert SH	
MARIE-LAURE MOENS	16/07/2024		1	LATTE	2,00 €	2,00 €			
MATTHEW WALKER	16/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	16/07/2024		1	LATTE GLACE AVOINE	2,50 €	2,50 €			
PHILIPPE LANDES	16/07/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
UGO DE LUCA	16/07/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	16/07/2024		1	CHAÏ GLACE	2,50 €	2,50 €			
VIRGINIE DEL RIEU	16/07/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
CAROLINE TERGEMINA	17/07/2024	TECHNIA	1	MATCHA LATTE	2,00 €	2,00 €			
ELENA PERROUIN	17/07/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JULIEN CARVAJAL	17/07/2024	TECHNIA	1	GENMAÏCHA	2,00 €	2,00 €			
MELINE BOUYSSI	17/07/2024		1	MOCACCINO	2,00 €	2,00 €			
OUARDIA EL BONNOUHI	17/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VALENTIN RENAUD	17/07/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
ANTHONY FELIN	18/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENJAMIN LEVESQUE	18/07/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	18/07/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CELINE LASBATX	18/07/2024		1	AEROCANO	1,50 €	1,50 €			
JEAN CIAPA	18/07/2024		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €	XL		
JULIE DENAT	18/07/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
MATTHIEU CROUZET	18/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	18/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PHILIPPE LANDES	18/07/2024		1	LATTE GLACE AVOINE	3,00 €	3,00 €	XL		
SARAH VIGUIE	18/07/2024		1	LATTE GLACE AVOINE	3,00 €	3,00 €	XL		
VALENTIN RENAUD	18/07/2024		1	AEROCANO	1,50 €	1,50 €			
ADRIEN MORQUE	19/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	19/07/2024		1	AEROCANO	1,50 €	1,50 €			
EMMANUELLE VAN DEN STEEN	19/07/2024		1	AEROCANO	1,50 €	1,50 €			
MATTHIEU CROUZET	19/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BARNABE LEVARD	22/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CLAIRE BELLOC	22/07/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ADRIEN MORQUE	23/07/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ANTHONY FELIN	23/07/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
ARNAUD THOMAS-SERVAIS	23/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
BARNABE LEVARD	23/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JULIE COUSSE	23/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
JULIE COUSSE	23/07/2024		1	LION*	1,00 €	1,00 €		Offert SH	
MATTHEW WALKER	23/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	23/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MOUAD BELGHITI	23/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	23/07/2024		1	LATTE GLACE AVOINE	2,50 €	2,50 €			
YANN KALECINSKI	23/07/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
OUARDIA EL BONNOUHI	24/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	25/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIANA ROA	25/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ANTHONY FELIN	25/07/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
ARNAUD THOMAS-SERVAIS	25/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
BARNABE LEVARD	25/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CELINE LASBATX	25/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
CHANTAL PERDIGAU	25/07/2024		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €			
DAVID BONNET	25/07/2024		1	LATTE	2,00 €	2,00 €			
LUCIE SCHAMING	25/07/2024		1	AEROCANO	1,50 €	1,50 €			
MARINA CASALE	25/07/2024		1	MATCHA LATTE GLACE	2,50 €	2,50 €			
MATTHIEU CROUZET	25/07/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
ADRIEN MORQUE	26/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BARNABE LEVARD	26/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	26/07/2024		1	CAPPUCCINO GLACE	2,00 €	2,00 €			
FRANCOIS BREMOND	26/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
LOUIS MELLIORAT	26/07/2024		1	AEROCANO	1,50 €	1,50 €		Offert SH	
MATTHIEU CROUZET	26/07/2024		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €			
OUARDIA EL BONNOUHI	26/07/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PHILIPPE ROLAND	26/07/2024	METAVONICS	1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €		Offert SH	
SEBASTIEN FLOCHLAY	26/07/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
ANGELIQUE FOUIX	29/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
ARNAUD THOMAS-SERVAIS	29/07/2024		2	CAFE FRAPPE	1,50 €	3,00 €			
CEDRIC BOUCHE	29/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
CHANTAL PERDIGAU	29/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
LUCIE SCHAMING	29/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
AGUSTINA WEBER	30/07/2024		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €			
ARNAUD THOMAS-SERVAIS	30/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
CHARLES GIAFFERI	30/07/2024		2	CAFE FRAPPE	1,50 €	3,00 €			
GREGOIRE CORBIERE	30/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
JOCHEN GRUNBECK	30/07/2024		1	CAPPUCCINO GLACE	2,00 €	2,00 €			
JULIE DENAT	30/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
PAULINE SARDA	30/07/2024		2	LATTE GLACE	2,50 €	5,00 €			
ABDEL HALIMI	31/07/2024		1	LATTE	2,00 €	2,00 €			
ANGELIQUE FOUIX	31/07/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
CHANTAL PERDIGAU	31/07/2024		1	LATTE GLACE	2,50 €	2,50 €			
JOCHEN GRUNBECK	31/07/2024		1	CAPPUCCINO GLACE	2,00 €	2,00 €
        ?	01/08/2024	BRYO	1	AEROCANO	1,50 €	1,50 €		Offert SH	
BENOIT COUX	01/08/2024		1	AEROCANO	1,50 €	1,50 €			
CHRISTOPHE BOUE	01/08/2024	BRYO	1	CAFE FRAPPE	1,50 €	1,50 €			
MARIE-LAURE MOENS	01/08/2024		1	LATTE MACCHIATO	2,50 €	2,50 €			
MARINA CASALE	01/08/2024		1	MATCHA LATTE GLACE	2,50 €	2,50 €			
MOUAD BELGHITI	01/08/2024		1	MOCACCINO	2,00 €	2,00 €			
PHILIPPE LANDES	01/08/2024		1	LATTE GLACE (VANILLE)	3,00 €	3,00 €	XL		
SEBASTIEN FLOCHLAY	01/08/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
ADRIEN MORQUE	02/08/2024		1	LATTE GLACE VIETNAMIEN	2,50 €	2,50 €			
AGUSTINA WEBER	02/08/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	05/08/2024		1	AEROCANO	1,50 €	1,50 €			
CORENTIN DOUAY	05/08/2024	METAVONICS	1	CHAÏ LATTE	2,00 €	2,00 €			
IVANA SAILHAN	05/08/2024	METAVONICS	1	CAPPUCCINO	1,50 €	1,50 €		Offert SH	
LUCIEN THIRIET	05/08/2024	METAVONICS	1	CHAÏ LATTE	2,00 €	2,00 €			
PHILIPPE LANDES	05/08/2024		1	LATTE GLACE AVOINE	2,50 €	2,50 €			
SEBASTIEN FLOCHLAY	05/08/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
SEBASTIEN FLOCHLAY	05/08/2024		1	MATCHA LATTE	2,00 €	2,00 €			
AGUSTINA WEBER	06/08/2024		1	CAPPUCCINO GLACE	2,00 €	2,00 €			
ANTHONY FELIN	06/08/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
CASSANDRA BONNAFOUS	06/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
CORENTIN DOUAY	06/08/2024	METAVONICS	1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	06/08/2024		1	DOUBLE AFFOGATO	2,50 €	2,50 €			
IVANA SAILHAN	06/08/2024	METAVONICS	1	CAPPUCCINO	1,50 €	1,50 €		Offert SH	
JOCHEN GRUNBECK	06/08/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	06/08/2024		2	LATTE GLACE	3,00 €	6,00 €	XL		
MELINE BOUYSSI	06/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
ANAEL MEGNA	07/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
ANTHONY FELIN	07/08/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
LAURE SARDELLA	07/08/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MATTHIEU CROUZET	07/08/2024		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €	XL		
ADRIEN MORQUE	08/08/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
CHRISTOPHE BOUE	08/08/2024	BRYO	1	LATTE GLACE	2,50 €	2,50 €			
ELIA MONGUILLON	08/08/2024		1	CHAÏ GLACE	3,00 €	3,00 €	XL	Offert SH	
LUCIEN THIRIET	08/08/2024	METAVONICS	2	MATCHA LATTE	2,00 €	4,00 €			
MATTHIEU CROUZET	08/08/2024		1	LATTE GLACE	3,00 €	3,00 €	XL		
PHILIPPE LANDES	08/08/2024		2	LATTE GLACE	2,50 €	5,00 €			
ADRIEN MORQUE	09/08/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ALEXIS DAMIENS	09/08/2024		1	GENMAÏCHA	2,00 €	2,00 €			
MATTHIEU CROUZET	09/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
MELINE BOUYSSI	09/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
ELENA PERROUIN	19/08/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JOCHEN GRUNBECK	20/08/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	20/08/2024		1	LATTE GLACE (VANILLE)	3,00 €	3,00 €	XL		
AGUSTINA WEBER	21/08/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ANAEL MEGNA	21/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
FADEL DIENE	21/08/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
FRANCOIS HELLOCO	21/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
JEANNE ROBIN	21/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
NATHALIE GRENET	21/08/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
PAULINE SARDA	21/08/2024		1	LATTE GLACE	2,50 €	2,50 €			
ELENA PERROUIN	22/08/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MATTHIEU CROUZET	22/08/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	XL		
ALEXIS DAMIENS	23/08/2024		1	GENMAÏCHA	2,00 €	2,00 €			
MATTHIEU CROUZET	23/08/2024		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €	XL		
PAULINE SARDA	23/08/2024		1	LATTE GLACE	2,50 €	2,50 €			
PHILIPPE LANDES	23/08/2024		2	CHAÏ GLACE	3,00 €	6,00 €	XL		
SAVANNAH SERVANT	23/08/2024	CAPGEMINI	1	CHAÏ LATTE	2,50 €	2,50 €	XL	Offert SH	
VALERIE ALASLUQUETAS	23/08/2024	YUKAN	1	LATTE	2,00 €	2,00 €		Offert SH	
ANASTASIA DE SANTIS	26/08/2024		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €	XL		
ANGELIQUE FOUIX	26/08/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	26/08/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
HELENE FABRE	26/08/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEANNE ROBIN	26/08/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
BARNABE LEVARD	27/08/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	27/08/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	27/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
JEANNE ROBIN	27/08/2024		1	CHOCOLAT GLACE	1,50 €	1,50 €	XXL		
LAETITIA RUAULT DURAND	27/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
MATTHIEU CROUZET	27/08/2024		2	CHOCOLAT CHAUD	0,50 €	1,00 €	L		
MATTHIEU CROUZET	27/08/2024		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €	XL		
PAULINE SARDA	27/08/2024		1	LATTE GLACE	2,50 €	2,50 €			
PHILIPPE LANDES	27/08/2024		1	LATTE GLACE (COOKIE)	2,50 €	2,50 €			
UGO DE LUCA	27/08/2024		1	CAFE FRAPPE	1,50 €	1,50 €			
UGO DE LUCA	27/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
VIRGINIE DEL RIEU	27/08/2024		1	VANILLA CREAM COLD BREW	2,00 €	2,00 €			
ANGELIQUE FOUIX	28/08/2024		1	AEROCANO	1,50 €	1,50 €			
BENOIT COUX	28/08/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
HELENE FABRE	28/08/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
LAURE SARDELLA	28/08/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MATTHIEU CROUZET	28/08/2024		1	MOCACCINO	2,50 €	2,50 €	L		
SOLENE ROSSARD	28/08/2024	TECHNIA	1	LATTE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	28/08/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLAIRE HADJADJ	29/08/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ELODIE ALVES	29/08/2024		1	AEROCANO	1,50 €	1,50 €			
FLORIAN DEVASSE	29/08/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
HELENE FABRE	29/08/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
KRISTELL THOMAS	29/08/2024		1	AEROCANO	1,50 €	1,50 €			
KRISTELL THOMAS	29/08/2024		1	MATCHA LATTE	2,00 €	2,00 €			
MATTHIEU CROUZET	29/08/2024		1	MOCACCINO	2,50 €	2,50 €	L		
PHILIPPE LANDES	29/08/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
ALEXIS DAMIENS	30/08/2024		1	GENMAÏCHA	2,00 €	2,00 €			
MATTHIEU CROUZET	30/08/2024		1	MOCACCINO	3,50 €	3,50 €	XXL (shot sup)		
MELINE BOUYSSI	30/08/2024		1	CHOCOLAT GLACE	1,50 €	1,50 €	XXL		
PHILIPPE LANDES	30/08/2024		1	CHAÏ AVOINE	2,50 €	2,50 €	L		
VALERIE ALASLUQUETAS	30/08/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	02/09/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CLARA MANHES	02/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
GLORIA FRADIN	02/09/2024		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €	XL		
MOUAD BELGHITI	02/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VALERIE ALASLUQUETAS	02/09/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VLAD CERISIER	02/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BARNABE LEVARD	03/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CEDRIC BOUCHE	03/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	03/09/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	03/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
ELENA PERROUIN	03/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	03/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MATTHEW WALKER	03/09/2024		2	MACCHIATO/NOISETTE	1,00 €	2,00 €			
PAULINE SARDA	03/09/2024		1	LATTE GLACE	2,50 €	2,50 €			
THAÏSS OYINI	03/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	03/09/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	03/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	03/09/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VLAD CERISIER	03/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AGUSTINA WEBER	04/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	04/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	04/09/2024		1	MOCACCINO	2,00 €	2,00 €			
BARNABE LEVARD	04/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BARNABE LEVARD	04/09/2024		1	MOCA	1,00 €	1,00 €			
BENOIT COUX	04/09/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CLARA MANHES	04/09/2024	YUKAN	1	MOCACCINO	2,00 €	2,00 €			
ELENA PERROUIN	04/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEANNE ROBIN	04/09/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
JUNIOR KABORE	04/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JUNIOR KABORE	04/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
LAURE SARDELLA	04/09/2024	TECHNIA	1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	04/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
PHILIPPE LANDES	04/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
THAÏSS OYINI	04/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VLAD CERISIER	04/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	05/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	05/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
CHRISTOPHE BOUE	05/09/2024	BRYO	1	LATTE MACCHIATO	2,50 €	2,50 €			
CLARA MANHES	05/09/2024	YUKAN	1	LATTE MACCHIATO (VANILLE)	3,00 €	3,00 €	DOUBLE SHOT		
ELENA PERROUIN	05/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JUNIOR KABORE	05/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JUNIOR KABORE	05/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MATTHEW WALKER	05/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHEW WALKER	05/09/2024		1	LATTE	2,00 €	2,00 €			
BARNABE LEVARD	06/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CEDRIC BOUCHE	06/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	06/09/2024	YUKAN	1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
MELODIE TYLER	06/09/2024		1	LATTE	2,00 €	2,00 €			
NATHALIE GRENET	06/09/2024		2	CAPPUCCINO	1,50 €	3,00 €			
OUARDIA EL BONNOUHI	06/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
THAÏSS OYINI	06/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VLAD CERISIER	06/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BARNABE LEVARD	09/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CEDRIC BOUCHE	09/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CEDRIC BOUCHE	09/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLARA MANHES	09/09/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VALERIE ALASLUQUETAS	09/09/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
VLAD CERISIER	09/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ASHLEY TSE	10/09/2024	METAVONICS	1	FLAT WHITE	2,00 €	2,00 €			
ASHLEY TSE	10/09/2024	METAVONICS	1	GRANOLA BOWL	4,50 €	4,50 €			
BAPTISTE MATHUS	10/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BARNABE LEVARD	10/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLARA MANHES	10/09/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	10/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	10/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JEAN CIAPA	10/09/2024		1	MOCACCINO GLACE	2,50 €	2,50 €			
MARGAUX DEROSIER	10/09/2024		1	LATTE MACCHIATO (CARAMEL)	3,00 €	3,00 €	DOUBLE SHOT		
MELODIE TYLER	10/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
MELODIE TYLER	10/09/2024		1	MATCHA SODA	2,50 €	2,50 €			
UGO DE LUCA	10/09/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	10/09/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
VIRGINIE DEL RIEU	10/09/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
BAPTISTE MATHUS	11/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	11/09/2024		1	MOCACCINO	2,00 €	2,00 €			
CLAIRE DHOOSCHE	11/09/2024		1	MATCHA LATTE	2,00 €	2,00 €		Offert SH	
CLARA MANHES	11/09/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
EMMANUELLE VAN DEN STEEN	11/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
LAURE SARDELLA	11/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
THAÏSS OYINI	11/09/2024		1	LATTE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	11/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VLAD CERISIER	11/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	12/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ELENA PERROUIN	12/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
GAUTHIER JOLLY	12/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
JEAN CIAPA	12/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JUNIOR KABORE	12/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
LAURE SARDELLA	12/09/2024	TECHNIA	1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	12/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MAEL VALAIS	12/09/2024		1	LATTE	2,00 €	2,00 €			
MARIE-LAURE MOENS	12/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
NATHALIE GRENET	12/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
PHILIPPE LANDES	12/09/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
QUENTIN PANLOUP	12/09/2024		1	LATTE GLACE VIETNAMIEN	2,50 €	2,50 €			
SOFIE LEON	12/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BAPTISTE MATHUS	13/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
ELENA PERROUIN	13/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
MOUAD BELGHITI	13/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
NATHALIE GRENET	13/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
OUARDIA EL BONNOUHI	13/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VALENTIN RENAUD	13/09/2024		1	MOCACCINO	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	13/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VLAD CERISIER	13/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
AGUSTINA WEBER	16/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BARNABE LEVARD	16/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CEDRIC BOUCHE	16/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	16/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
MATTHIEU CROUZET	16/09/2024		1	LATTE MACCHIATO (COOKIES)	3,00 €	3,00 €	XL		
THAÏSS OYINI	16/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	16/09/2024	YUKAN	2	CAPPUCCINO	1,50 €	3,00 €			
VLAD CERISIER	16/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	17/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	17/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MATTHIEU CROUZET	17/09/2024		1	LATTE MACCHIATO (COOKIES)	3,00 €	3,00 €	XL		
NATHALIE GRENET	17/09/2024		1	MACCHIATO/NOISETTE AVOINE	1,00 €	1,00 €			
THAÏSS OYINI	17/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	17/09/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	17/09/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	18/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	18/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
CASSANDRE DORET	18/09/2024	OYA YOGA	1	CAPPUCCINO	1,50 €	1,50 €			
CASSANDRE DORET	18/09/2024	OYA YOGA	1	MATCHA LATTE	2,00 €	2,00 €			
CHRISTELLE LAGAE	18/09/2024	1000CAFES	1	FLAT WHITE	2,00 €	2,00 €			
ELENA PERROUIN	18/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
LAURE SARDELLA	18/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
PAULINE SARDA	18/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	18/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
PHILIPPE LANDES	18/09/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
THOMAS LECHEVALIER	18/09/2024		1	MOCA	1,00 €	1,00 €			
VLAD CERISIER	18/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ADRIEN MORQUE	19/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BAPTISTE MATHUS	19/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BARNABE LEVARD	19/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	19/09/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CHRISTOPHE BOUE	19/09/2024	BRYO	2	LATTE MACCHIATO	2,50 €	5,00 €			
CLARA MANHES	19/09/2024	YUKAN	1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
JUNIOR KABORE	19/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
LAURE SARDELLA	19/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MATTHIEU CROUZET	19/09/2024		1	MOCACCINO	2,00 €	2,00 €			
MELODIE TYLER	19/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
MELODIE TYLER	19/09/2024		1	MATCHA LATTE	2,00 €	2,00 €			
SARAH VIGUIE	19/09/2024		1	LATTE	2,00 €	2,00 €			
THAÏSS OYINI	19/09/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
THOMAS LECHEVALIER	19/09/2024		1	MOCACCINO AVOINE	2,50 €	2,50 €	L		
UGO DE LUCA	19/09/2024		2	CHAÏ AVOINE	2,00 €	4,00 €			
VALERIE ALASLUQUETAS	19/09/2024		1	LATTE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	19/09/2024		2	CHAÏ AVOINE	2,00 €	4,00 €			
VLAD CERISIER	19/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ADRIEN MORQUE	20/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BENOIT COUX	20/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CLARA MANHES	20/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
ELENA PERROUIN	20/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
FRANCOIS BREMOND	20/09/2024		1	MOCACCINO	2,00 €	2,00 €			
NATHALIE GRENET	20/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
OUARDIA EL BONNOUHI	20/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
BENOIT COUX	23/09/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CASSANDRE DORET	23/09/2024	OYA YOGA	1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MAELIA LEGRAND	23/09/2024		1	MATCHA LATTE AVOINE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	23/09/2024	YUKAN	1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
VLAD CERISIER	23/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ABDEL HALIMI	24/09/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	24/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BARNABE LEVARD	24/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CHRISTELLE LAGAE	24/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
CORENTIN DOUAY	24/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	24/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
EMMANUELLE BIADI-COMET	24/09/2024		1	MATCHA SODA	2,50 €	2,50 €			
MAELIA LEGRAND	24/09/2024		1	MATCHA LATTE GLACE	2,50 €	2,50 €	GLACE		
VLAD CERISIER	24/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BAPTISTE MATHUS	25/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	25/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
CORENTIN DOUAY	25/09/2024	METAVONICS	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
LAURE SARDELLA	25/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MARTIAL MONTRICHARD	25/09/2024	METAVONICS	1	AEROCANO	1,50 €	1,50 €		CAMILLE (seminaire)	
MARTIAL MONTRICHARD	25/09/2024	METAVONICS	1	MACCHIATO/NOISETTE AVOINE	1,00 €	1,00 €		MARC (seminaire)	
OUARDIA EL BONNOUHI	25/09/2024		1	CHOCOLAT GLACE	0,50 €	0,50 €			
BAPTISTE MATHUS	26/09/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
ELENA PERROUIN	26/09/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JOCHEN GRUNBECK	26/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LAURE SARDELLA	26/09/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
LAURE SARDELLA	26/09/2024	TECHNIA	1	CHAÏ LATTE	2,00 €	2,00 €			
LAURE SARDELLA	26/09/2024	TECHNIA	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
MARTIAL MONTRICHARD	26/09/2024	METAVONICS	1	AEROCANO	1,50 €	1,50 €		LIYAN (seminaire)	
SARAH VIGUIE	26/09/2024		1	LATTE	2,00 €	2,00 €			
BARNABE LEVARD	27/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENJAMIN CORRET	27/09/2024		1	THE MATCHA	2,00 €	2,00 €			
CLARA MANHES	27/09/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
CORENTIN DOUAY	27/09/2024	METAVONICS	1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
ERIC GUIN	27/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LUCIEN THIRIET	27/09/2024	METAVONICS	1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €			
VALERIE ALASLUQUETAS	27/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
VLAD CERISIER	27/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BARNABE LEVARD	30/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLAIRE HADJADJ	30/09/2024		1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	30/09/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
MATTHEW WALKER	30/09/2024		1	LATTE	2,00 €	2,00 €			
MELODIE TYLER	30/09/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
VALERIE ALASLUQUETAS	30/09/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
AGUSTINA WEBER	01/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
BAPTISTE MATHUS	01/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CASSANDRE DORET	01/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CASSANDRE DORET	01/10/2024		1	LATTE GLACE VIETNAMIEN	2,50 €	2,50 €			
CLARA MANHES	01/10/2024	YUKAN	1	LATTE	2,00 €	2,00 €			
DAMIEN MATHIEU	01/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €		(PAULINE)	
ELENA PERROUIN	01/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
GLORIA FRADIN	01/10/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
JEAN CIAPA	01/10/2024		2	CAPPUCCINO	1,50 €	3,00 €			
BENOIT COUX	02/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CEDRIC BOUCHE	02/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	02/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
IRIS BORRUT	02/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
MELODIE TYLER	02/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
OUARDIA EL BONNOUHI	02/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	02/10/2024		2	CAPPUCCINO	1,50 €	3,00 €			
BAPTISTE MATHUS	03/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BENOIT COUX	03/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
ELENA PERROUIN	03/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	03/10/2024		1	BABYCCINO	0,50 €	0,50 €			
LAURE SARDELLA	03/10/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
ADRIANA ROA	04/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ADRIEN MORQUE	04/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
ANASTASIA DE SANTIS	04/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
CLEMENTINE CABROL	04/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MANU DEJEAN	04/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
MOUAD BELGHITI	04/10/2024		1	MOCACCINO	2,00 €	2,00 €			
PHILIPPE LANDES	04/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
SEBASTIEN FLOCHLAY	04/10/2024		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €			
CLAIRE HADJADJ	07/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	07/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
NATHALIE GRENET	07/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ARIADNA MATAS	08/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
BENOIT COUX	08/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CEDRIC BOUCHE	08/10/2024		1	FLAT WHITE	2,00 €	2,00 €			
DAVID BONNET	08/10/2024		1	LATTE	2,00 €	2,00 €			
DAVID DAIGNAN	08/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
JEAN CIAPA	08/10/2024		1	CAPPUCCINO	1,50 €	1,50 €		(visiteur)	
JEAN CIAPA	08/10/2024		1	BABYCCINO	0,50 €	0,50 €			
JEANNE ROBIN	08/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MATTHIEU CROUZET	08/10/2024		1	MOCACCINO	2,00 €	2,00 €			
PHILIPPE LANDES	08/10/2024		3	CHAÏ LATTE	2,00 €	6,00 €			
VIRGINIE DEL RIEU	08/10/2024		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
BAPTISTE MATHUS	09/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BARNABE LEVARD	09/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	09/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CASSANDRE DORET	09/10/2024	OYA YOGA	1	CAPPUCCINO	1,50 €	1,50 €			
CHRISTOPHE BOUE	09/10/2024	BRYO	3	LATTE MACCHIATO	2,50 €	7,50 €			
CHRISTOPHE BOUE	09/10/2024	BRYO	1	AEROCANO	1,50 €	1,50 €			
CHRISTOPHE BOUE	09/10/2024	BRYO	1	CHAÏ LATTE	2,00 €	2,00 €			
EDGAR RODRIGUES	09/10/2024	ITERNET	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
LAURE SARDELLA	09/10/2024	TECHNIA	1	ESPRESSO TONIC	2,00 €	2,00 €			
MARGAUX ARTUSO	09/10/2024	ITERNET	1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
MATTHIEU CROUZET	09/10/2024		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €			
MAX LEVER	09/10/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	PIMENT		
MELODIE TYLER	09/10/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	PIMENT		
NICOLAS NAUDY	09/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €		Offert SH	
NICOLAS NAUDY	09/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €		Offert SH	
VLAD CERISIER	09/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BAPTISTE MATHUS	10/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BARNABE LEVARD	10/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	10/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CASSANDRA BONNAFOUS	10/10/2024		1	CHOCOLAT CHAUD	0,50 €	0,50 €	PIMENT		
CHADI LAJMI	10/10/2024	EVOLIO	1	CAPPUCCINO	1,50 €	1,50 €			
CHRISTOPHE BOUE	10/10/2024		1	LATTE MACCHIATO	2,50 €	2,50 €			
CHRISTOPHE BOUE	10/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ELENA PERROUIN	10/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEANNE ROBIN	10/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
LAETITIA RUAULT DURAND	10/10/2024		1	MOCACCINO	2,00 €	2,00 €			
LAURE SARDELLA	10/10/2024	TECHNIA	1	CAPPUCCINO	1,50 €	1,50 €			
NICOLAS NAUDY	10/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €		Offert SH	
NICOLAS NAUDY	10/10/2024		1	CAPPUCCINO	1,50 €	1,50 €		Offert SH	
NICOLAS NAUDY	10/10/2024		1	PAGO*	1,50 €	1,50 €		Offert SH	
SARAH VIGUIE	10/10/2024		1	LATTE	2,00 €	2,00 €			
SOLENE ROSSARD	10/10/2024	TECHNIA	1	LATTE	2,00 €	2,00 €			
UGO DE LUCA	10/10/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VLAD CERISIER	10/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ADRIANA ROA	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ARNAUD THOMAS-SERVAIS	11/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
CHANTAL PERDIGAU	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
EDGAR RODRIGUES	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JOCHEN GRUNBECK	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JOCHEN GRUNBECK	11/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
JULIE CARTIGNY	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
MARGAUX ARTUSO	11/10/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
VALERIE ALASLUQUETAS	11/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
VLAD CERISIER	11/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
ANGELIQUE FOUIX	14/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
BARNABE LEVARD	14/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLARA MANHES	14/10/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
CLARA MANHES	14/10/2024	YUKAN	1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
JEANNE ROBIN	14/10/2024		1	MOCACCINO	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	14/10/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
VLAD CERISIER	14/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
AGUSTINA WEBER	15/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
AMINE EL AZIZI	15/10/2024		1	FLAT WHITE	2,00 €	2,00 €			
AURELIE PICHOT	15/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	15/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BENOIT COUX	15/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
CLARA MANHES	15/10/2024	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	15/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
JEAN CIAPA	15/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
JOCHEN GRUNBECK	15/10/2024		2	MACCHIATO/NOISETTE	1,00 €	2,00 €			
LAETITIA RUAULT DURAND	15/10/2024		1	MOCACCINO	2,00 €	2,00 €			
MELODIE TYLER	15/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
UGO DE LUCA	15/10/2024		1	CHAÏ LATTE	2,00 €	2,00 €			
UGO DE LUCA	15/10/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	15/10/2024		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
VIRGINIE DEL RIEU	15/10/2024		1	CHAÏ AVOINE	2,00 €	2,00 €			
AMINE EL AZIZI	16/10/2024		1	FLAT WHITE	2,00 €	2,00 €			
BARNABE LEVARD	16/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
BENOIT COUX	16/10/2024		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €			
LAURE SARDELLA	16/10/2024		1	ESPRESSO TONIC	2,00 €	2,00 €			
MARYLENE LAURENT	16/10/2024		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €			
OUARDIA EL BONNOUHI	16/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
PAULINE SARDA	16/10/2024		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €			
PAULINE SARDA	16/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
SOLENE ROSSARD	16/10/2024		1	LATTE	2,00 €	2,00 €			
SOPHIE JAMAIN	16/10/2024		1	LATTE	2,00 €	2,00 €			
ADRIEN MORQUE	17/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
AGUSTINA WEBER	17/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
AMINE EL AZIZI	17/10/2024		1	FLAT WHITE	2,00 €	2,00 €			
AMINE EL AZIZI	17/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
AURELIE PICHOT	17/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
BAPTISTE MATHUS	17/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
CHRISTOPHE BOUE	17/10/2024		1	LATTE MACCHIATO	2,50 €	2,50 €			
CHRISTOPHE BOUE	17/10/2024		1	MACCHIATO/NOISETTE	1,00 €	1,00 €			
CLAIRE BELLOC	17/10/2024		1	LATTE	2,00 €	2,00 €			
CLARA MANHES	17/10/2024		1	CAPPUCCINO	1,50 €	1,50 €			
ELENA PERROUIN	17/10/2024		1	DOUBLE ESPRESSO	0,50 €	0,50 €			
ELENA PERROUIN	17/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
JEAN CIAPA	17/10/2024		1	CAPPUCCINO AVOINE	1,50 €	1,50 €			
MATTHIEU CROUZET	17/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
MOUAD BELGHITI	17/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
NATHALIE GRENET	17/10/2024		2	MACCHIATO/NOISETTE	1,00 €	2,00 €			
SARAH VIGUIE	17/10/2024		1	LATTE	2,00 €	2,00 €			
VALERIE ALASLUQUETAS	17/10/2024		1	FLAT WHITE	2,00 €	2,00 €			
VIRGINIE DEL RIEU	17/10/2024		1	CHAÏ AVOINE	3,50 €	3,50 €	XXL		
BAPTISTE MATHUS	18/10/2024		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €			
MELODIE TYLER	18/10/2024		1	GRANOLA BOWL	4,50 €	4,50 €			
CLAIRE BELLOC	18/10/2024		1	LATTE	2,00 €	2,00 €			
THOMAS LECHEVALIER	18/10/2024		2	CAPPUCCINO AVOINE	1,50 €	3,00 €';/*

        NICOLAS NAUDY	10/10/2024		1	PAGO*	1,50 €	1,50 €
        MARIE-LAURE MOENS	16/07/2024		1	BOUNTY*	1,00 €	1,00 €
        JULIE COUSSE	23/07/2024		1	LION*	1,00 €	1,00 €

         */;
    }

}
