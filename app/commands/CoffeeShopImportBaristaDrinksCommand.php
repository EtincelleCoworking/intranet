<?php

use Illuminate\Console\Command;

class CoffeeShopImportBaristaDrinksCommand extends Command
{
    const FIELD_USER = 0;
    const FIELD_DATE = 1;
    const FIELD_QUANTITY = 4;
    const FIELD_PRODUCT = 5;
    const FIELD_UNIT_PRICE = 6;
    const FIELD_TOTAL_PRICE = 7;
    const FIELD_ADDON = 8;
    const FIELD_COMMENT = 9;

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
                    $price = $tokens[self::FIELD_TOTAL_PRICE];
                    if (0 == $price) {
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
                            $order->product_slug = 'hot-drinks.' . $product;
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
            case 'CHRISTIAN RAKOTONDRAINIBE':
                return 7247;
            case 'MARIA CHOUPPARD':
                return 7211;
            case 'CECILE BARTHES':
                return 7250;
            case 'DOMITILLE GALLI':
                return 7159;
            case 'SARA TISSENIER':
                return 7228;
            case 'JULIEN COUTURIER':
                return 419;
            case 'MOHAMED ELADL':
                return 5926;
            case 'IMENE THAMRI':
                return 6176;
            case 'LOUIS ULMER':
                return 7240;
            case 'MATHIEU FELIX':
                return 6643;
            case 'ELSA CARDINAUD':
                return 7229;
            case 'MATHIEU LECOQ':
                return 1018;
            case 'DIDIER LAHAY':
                return 7190;
            case 'MARION DESCLAUX':
                return 7175;
            case 'VICTORIA PUYUELO':
                return 7301;
            case 'ARTHUR SUDRE':
                return 4454;
            case 'MICKAEL DE CONINCK':
                return 7310;
            case 'SOLENE LAYBROS':
                return 4661;
            case 'JEAN-CHARLES ROUSSEAU':
                return 5489;
            case 'BENJAMIN MERIEAU':
                return 5637;
            case 'LUCILE BAILLOU':
                return 7322;
            case 'MANON OLIVIER':
                return 7291;
            case 'LAURA ARLES':
                return 7304;
            case 'JEAN REMI ROUX':
                return 6936;
            case 'MOHAMED ZAAROUR':
                return 7108;
            case 'ELODIE BAROT':
                return 3287;
            case 'MATTHIAS BRIGAUD':
                return 6850;
            case 'DORINE JUBERTIE':
                return 7364;
            case 'ANDRES GOMEZ':
                return 4587;
            case 'ANAE LEFEVRE':
                return 6018;
            case 'VASCO COMPAIN':
                return 5901;
            case 'SOPHIE DESBONNEZ':
                return 4093;
            case 'THOMAS GONZALEZ':
                return 7200;
            case 'COLINE DACLIN':
                return 7266;
            case 'THOMAS NGOMA':
                return 7355;
            case 'SEBASTIEN KERHERVE':
                return 5620;
            case 'KEAN DEQUEANT':
                return 5521;
            case 'EMMA CADIER':
                return 7337;
            case 'SADRI LASSOUED':
                return 5938;
            case 'CEDRIC SIGNE MBE':
                return 7365;
            case 'JOAQUIN SPRENG':
                return 7330;
            case 'VINCENT DEBRAY':
                return 7383;
            case 'CAMILLE BORDIGNON':
                return 7381;
            case 'MORGANE BOUSQUET':
                return 7378;
            case 'LUCIE CHEVALLIER':
                return 7415;
            case 'MATHILDE DE VOS':
                return 7319;
            case 'ORANE TREHET':
                return 7416;
            case 'JEREMY BELHADJ':
                return 7384;
            case 'BENOIT RIGOLLEAU':
                return 7382;
            case 'XAVIER MEUNIER': return 7541;
            case 'LOUIS JARDIN': return 7422;
            case 'GAELLE PAPPO': return 7367;
            case 'JOANNA CLOSA': return 7114;
            case 'SHUYAO ZHANG': return 7433;
            case 'SERGIO BELLON': return 7414;
            case 'OLIVIA SIGNOUREL': return 7542;
            case 'LENA PAWELCZYK': return 7543;
//            case 'SOPHIE BRUNET': return null; FAH ?
            case 'CELINE PRATX': return 7343;
            case 'VINCENT VENTALON': return 5151;
            case 'BENOIT GUINET': return 6808;
            case 'BENJAMIN THEYTAZ': return 7521;
            case 'PAULINE PONTIS': return 7455;
            case 'CLEYDYR BEZERRA': return 7544;
            case 'NOEMIE CALVET': return 7545;
//            case 'CEDRIC SIGNE MBE': return null;
            case 'QUENTIN LE GUILLERMIC': return 7546;

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
            'Boisson de la semaine' => 0,// ?
            'Scone vegan' => 3.5,// ?
            'Chocolat viennois' => 2.5,// ?
            'Fruits frais coupés' => 3.5,// ?
            'Affogato' => 2.5,// ?
            'Strawberry matcha latte' => 3.5,// ?
            'Peach Ginger Fizz' => 2.5,// ?
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
        return 'ELENA PERROUIN	05/05/2025	19		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CHRISTELLE LAGAE	05/05/2025	19	1000CAFES	1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CHANTAL PERDIGAU	05/05/2025	19		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	05/05/2025	19		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
VALERIE HAMEAU	05/05/2025	19		1	LATTE MACCHIATO	2,50 €	2,50 €																						
CLARA MANHES	05/05/2025	19	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €																						
MATTHEW WALKER	05/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
ERIC GUIN	05/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	05/05/2025	19		2	CHICORYCCINO	2,00 €	4,00 €	L																					
ANGELIQUE FOUIX	05/05/2025	19		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	05/05/2025	19		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/05/2025	19		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/05/2025	19		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/05/2025	19		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MAX LEVER	05/05/2025	19		1	CHICOREE	0,50 €	0,50 €																						
KEAN DEQUEANT	05/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
ELENA PERROUIN	06/05/2025	19		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
BAPTISTE MATHUS	06/05/2025	19		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
VICTORIA PUYUELO	06/05/2025	19		2	CAPPUCCINO	1,50 €	3,00 €																						
VIRGINIE DEL RIEU	06/05/2025	19		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
BENOIT COUX	06/05/2025	19		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
SOPHIE JAMAIN	06/05/2025	19		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
CLARA MANHES	06/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
ERIC GUIN	06/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	06/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	06/05/2025	19		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
CINDY HERAUD	06/05/2025	19		1	LATTE	2,00 €	2,00 €																						
SOPHIE JAMAIN	06/05/2025	19		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	06/05/2025	19		11	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/05/2025	19		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/05/2025	19		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/05/2025	19		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VIRGINIE DEL RIEU	06/05/2025	19		1	CHAÏ AVOINE	2,00 €	2,00 €																						
VALERIE ALASLUQUETAS	06/05/2025	19		1	CAPPUCCINO	2,00 €	2,00 €																						
COLINE DACLIN	06/05/2025	19		1	MATCHA LATTE	2,00 €	2,00 €																						
MAX LEVER	06/05/2025	19		1	CHICOREE	0,50 €	0,50 €																						
ELENA PERROUIN	07/05/2025	19		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
AGUSTINA WEBER	07/05/2025	19		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
PAULINE SARDA	07/05/2025	19		1	LATTE GLACE (VANILLE)	2,50 €	2,50 €		1 SHOT																				
SOPHIE JAMAIN	07/05/2025	19		1	LATTE	2,00 €	2,00 €																						
BENOIT RIGOLLEAU	07/05/2025	19	KONBOI.ONE	1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CAMILLE BORDIGNON	07/05/2025	19	KONBOI.ONE	1	CAPPUCCINO	1,50 €	1,50 €																						
KEAN DEQUEANT	07/05/2025	19		1	CAPPUCCINO	1,50 €	1,50 €																						
PAULINE SARDA	07/05/2025	19		1	CHAÏ LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	07/05/2025	19		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
SUIVI CONSO GRATUITE	07/05/2025	19		15	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/05/2025	19		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/05/2025	19		7	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/05/2025	19		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
JOCHEN GRUNBECK	07/05/2025	19		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
IMENE THAMRI	07/05/2025	19		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
FADEL DIENE	07/05/2025	19		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
ELENA PERROUIN	12/05/2025	20		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
VALERIE HAMEAU	12/05/2025	20		1	LATTE MACCHIATO	2,50 €	2,50 €																						
ANGELIQUE FOUIX	12/05/2025	20		1	FLAT WHITE	2,00 €	2,00 €																						
MANON OLIVIER	12/05/2025	20		1	OURS BLANC	2,50 €	2,50 €																						
LEANE DOMERGUE	12/05/2025	20		2	LATTE	2,00 €	4,00 €																						
CHRISTELLE LAGAE	12/05/2025	20		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
JEAN CIAPA	12/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	12/05/2025	20		1	LATTE	2,00 €	2,00 €																						
VALERIE HAMEAU	12/05/2025	20		1	LATTE MACCHIATO	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	12/05/2025	20		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/05/2025	20		19	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/05/2025	20		16	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/05/2025	20		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BUREAU HYBRIDE	12/05/2025	20		3	CAPPUCCINO	0,00 €	0,00 €		CF2i																				
VIRGINIE DEL RIEU	12/05/2025	20		1	DIRTY CHAÏ LATTE	4,00 €	4,00 €	XXL																					
JEAN CIAPA	12/05/2025	20		1	CHAÏ AVOINE	2,50 €	2,50 €																						
VALERIE HAMEAU	12/05/2025	20		1	CHICORYCCINO	2,00 €	2,00 €	L																					
OUARDIA EL BONNOUHI	12/05/2025	20		1	CAPPUCCINO AVOINE	2,50 €	2,50 €																						
ELENA PERROUIN	13/05/2025	20		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
UGO DE LUCA	13/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
MANON OLIVIER	13/05/2025	20		1	OURS BLANC	2,50 €	2,50 €																						
JEAN CIAPA	13/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	13/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
CLARA MANHES	13/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
SHUYAO ZHANG	13/05/2025	20		1	MATCHA LATTE	2,00 €	2,00 €																						
JOANNA CLOSA	13/05/2025	20		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	13/05/2025	20		9	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/05/2025	20		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/05/2025	20		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/05/2025	20		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BUREAU HYBRIDE	13/05/2025	20		1	CAPPUCCINO	0,00 €	0,00 €																						
CHANTAL PERDIGAU	14/05/2025	20		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
GAELLE PAPPO	14/05/2025	20		1	LATTE MACCHIATO	2,50 €	2,50 €																						
LAURE SARDELLA	14/05/2025	20		1	CHAÏ GLACE	2,50 €	2,50 €																						
LEANE DOMERGUE	14/05/2025	20		2	LATTE	2,00 €	4,00 €																						
VALERIE ALASLUQUETAS	14/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
KEAN DEQUEANT	14/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
SOPHIE JAMAIN	14/05/2025	20		1	LATTE	2,00 €	2,00 €																						
JULIE COUSSE	14/05/2025	20		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
LAURE SARDELLA	14/05/2025	20		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
SUIVI CONSO GRATUITE	14/05/2025	20		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/05/2025	20		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/05/2025	20		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/05/2025	20		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BUREAU HYBRIDE	14/05/2025	20		2	CAPPUCCINO	0,00 €	0,00 €																						
IMENE THAMRI	15/05/2025	20		1	LATTE	2,50 €	2,50 €	SHOT SUP																					
CHRISTOPHE BOUE	15/05/2025	20		2	LATTE MACCHIATO	2,50 €	5,00 €																						
BENOIT COUX	15/05/2025	20		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
ANTHONY FELIN	15/05/2025	20		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BARNABE LEVARD	15/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	15/05/2025	20		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SHUYAO ZHANG	15/05/2025	20		1	MATCHA LATTE	2,00 €	2,00 €																						
MANON OLIVIER	15/05/2025	20		1	OURS BLANC	2,50 €	2,50 €																						
ERIC GUIN	15/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
UGO DE LUCA	15/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	15/05/2025	20		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
SARAH VIGUIE	15/05/2025	20		1	LATTE	2,00 €	2,00 €																						
CHRISTOPHE BOUE	15/05/2025	20		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		FABRICE																				
CLARA MANHES	15/05/2025	20		1	LATTE	2,00 €	2,00 €																						
JEAN CIAPA	15/05/2025	20		1	OURS BLANC	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	15/05/2025	20		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/05/2025	20		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/05/2025	20		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/05/2025	20		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENJAMIN LEVESQUE	15/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
SOLENE LAYBROS	15/05/2025	20		1	GRANOLA BOWL	4,50 €	4,50 €																						
SARA TISSENIER	15/05/2025	20		1	LATTE	2,00 €	2,00 €																						
CHRISTOPHE BOUE	15/05/2025	20		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
CHRISTOPHE BOUE	15/05/2025	20		1	MOCA	1,00 €	1,00 €		FABRICE																				
SHUYAO ZHANG	15/05/2025	20		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
BAPTISTE MATHUS	15/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
LOUIS JARDIN	15/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
EMMA CADIER	16/05/2025	20		2	CAPPUCCINO	1,50 €	3,00 €																						
GREGORY ESTRADE	16/05/2025	20		1	LATTE	2,00 €	2,00 €																						
CHANTAL PERDIGAU	16/05/2025	20		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
AGUSTINA WEBER	16/05/2025	20		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
JEAN CIAPA	16/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
SHUYAO ZHANG	16/05/2025	20		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
ESTEVE PINYOL	16/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
PIERRE-MAEL MAYNE	16/05/2025	20		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
OUARDIA EL BONNOUHI	16/05/2025	20		1	CAPPUCCINO	1,50 €	1,50 €																						
JEAN CIAPA	16/05/2025	20		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
BAPTISTE MATHUS	16/05/2025	20		1	CHICORYCCINO	1,50 €	1,50 €																						
CHRISTELLE LAGAE	16/05/2025	20		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	16/05/2025	20		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/05/2025	20		11	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/05/2025	20		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/05/2025	20		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VALERIE HAMEAU	19/05/2025	21		1	LATTE MACCHIATO	2,50 €	2,50 €																						
MATTHIEU CROUZET	19/05/2025	21		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €																						
EMMA CADIER	19/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
SHUYAO ZHANG	19/05/2025	21		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
SOPHIE DESBONNEZ	19/05/2025	21		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
ANGELIQUE FOUIX	19/05/2025	21		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	SHOT SUP																					
NATHALIE GRENET	19/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CHANTAL PERDIGAU	19/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ERIC GUIN	19/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
KEAN DEQUEANT	19/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	19/05/2025	21		1	LATTE	2,00 €	2,00 €																						
VALERIE HAMEAU	19/05/2025	21		1	CHICORYCCINO	2,50 €	2,50 €	L																					
SARA TISSENIER	19/05/2025	21		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	19/05/2025	21		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/05/2025	21		23	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/05/2025	21		9	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/05/2025	21		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MARGAUX DEROSIER	20/05/2025	21		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
BENJAMIN LEVESQUE	20/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	20/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURA ARLES	20/05/2025	21		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
ORANE TREHET	20/05/2025	21		1	WHITE MATCHA LATTE	3,50 €	3,50 €	XL																					
SHUYAO ZHANG	20/05/2025	21		1	TIRAMISU LATTE 	3,00 €	3,00 €																						
OUARDIA EL BONNOUHI	20/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	20/05/2025	21		1	LATTE	2,00 €	2,00 €																						
LAURE SARDELLA	20/05/2025	21		1	MOCACCINO GLACE	2,50 €	2,50 €																						
JEAN CIAPA	20/05/2025	21		1	MOCACCINO	2,00 €	2,00 €																						
UGO DE LUCA	20/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	20/05/2025	21		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
KEAN DEQUEANT	20/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	20/05/2025	21		2	CAPPUCCINO	1,50 €	3,00 €																						
CINDY HERAUD	20/05/2025	21		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	20/05/2025	21		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/05/2025	21		16	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/05/2025	21		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/05/2025	21		6	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MOHAMED ZAAROUR	21/05/2025	21		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
PAULINE SARDA	21/05/2025	21		1	LATTE GLACE (VANILLE)	2,50 €	2,50 €		1 SHOT																				
BARNABE LEVARD	21/05/2025	21		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
MARGAUX DEROSIER	21/05/2025	21		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
CAMILLE BORDIGNON	21/05/2025	21	KONBOI.ONE	1	CAPPUCCINO	1,50 €	1,50 €																						
VINCENT DEBRAY	21/05/2025	21	KONBOI.ONE	1	CAPPUCCINO	1,50 €	1,50 €																						
BAPTISTE MATHUS	21/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
SHUYAO ZHANG	21/05/2025	21		1	CAFE VIENNOIS	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	21/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
SOPHIE JAMAIN	21/05/2025	21		1	LATTE	2,00 €	2,00 €																						
PAULINE PONTIS	21/05/2025	21		1	MOCACCINO	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
ERIC GUIN	21/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
MATTHEW WALKER	21/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	21/05/2025	21		1	CHAÏ GLACE	3,50 €	3,50 €	XL																					
KEAN DEQUEANT	21/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
SOPHIE DESBONNEZ	21/05/2025	21		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
PAULINE SARDA	21/05/2025	21		1	LATTE GLACE (VANILLE)	2,50 €	2,50 €		1 SHOT																				
SUIVI CONSO GRATUITE	21/05/2025	21		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/05/2025	21		26	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/05/2025	21		14	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/05/2025	21		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHANTAL PERDIGAU	22/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ELENA PERROUIN	22/05/2025	21		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CHRISTOPHE BOUE	22/05/2025	21		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		FABRICE																				
CHRISTOPHE BOUE	22/05/2025	21		2	LATTE MACCHIATO	2,50 €	5,00 €																						
SARAH VIGUIE	22/05/2025	21		1	LATTE	2,00 €	2,00 €																						
BENJAMIN MERIEAU	22/05/2025	21		1	FLAT WHITE	2,00 €	2,00 €																						
ADRIEN MORQUE	22/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	22/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SHUYAO ZHANG	22/05/2025	21		1	MOCACCINO GLACE	3,50 €	3,50 €	XL																					
ANAE LEFEVRE	22/05/2025	21		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	22/05/2025	21		9	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/05/2025	21		25	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/05/2025	21		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/05/2025	21		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SARA TISSENIER	22/05/2025	21		1	LATTE	2,00 €	2,00 €																						
CHRISTOPHE BOUE	22/05/2025	21		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		FABRICE																				
CINDY HERAUD	22/05/2025	21		1	LATTE	2,00 €	2,00 €																						
JULIE COUSSE	22/05/2025	21		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
CHRISTOPHE BOUE	22/05/2025	21		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
ADRIANA ROA	23/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
MOUAD BELGHITI	23/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
XAVIER MEUNIER	23/05/2025	21	OCTO	1	CAPPUCCINO	1,50 €	1,50 €		xavier.meunier@octo.com																				
SHUYAO ZHANG	23/05/2025	21		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
MANON OLIVIER	23/05/2025	21		1	OURS BLANC	2,50 €	2,50 €																						
BARNABE LEVARD	23/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	23/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
JEAN CIAPA	23/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
MELODIE TYLER	23/05/2025	21		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
PAULINE SARDA	23/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
COLLEEN HANRIOT	23/05/2025	21		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
ELODIE ALVES	23/05/2025	21		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
OUARDIA EL BONNOUHI	23/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CHANTAL PERDIGAU	23/05/2025	21		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SADRI LASSOUED	23/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
SERGIO BELLON	23/05/2025	21		1	CAPPUCCINO	1,50 €	1,50 €																						
JOHAN RITTERSHAUS	23/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
MAX LEVER	23/05/2025	21		1	CHICOREE	0,50 €	0,50 €																						
JEAN CIAPA	23/05/2025	21		1	CHICORYCCINO	1,50 €	1,50 €																						
OLIVIA SIGNOUREL	23/05/2025	21	OCTO	1	LATTE	2,00 €	2,00 €		signourel@gmail.com																				
SUIVI CONSO GRATUITE	23/05/2025	21		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/05/2025	21		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/05/2025	21		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/05/2025	21		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHANTAL PERDIGAU	26/05/2025	22		2	CAPPUCCINO AVOINE	1,50 €	3,00 €																						
CHRISTELLE LAGAE	26/05/2025	22		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SHUYAO ZHANG	26/05/2025	22		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
BARNABE LEVARD	26/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	26/05/2025	22		2	LATTE MACCHIATO	2,50 €	5,00 €																						
MELODIE DOUGNAC	26/05/2025	22		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ANAIS EL AOUD	26/05/2025	22		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
SOPHIE DESBONNEZ	26/05/2025	22		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
ANGELIQUE FOUIX	26/05/2025	22		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	SHOT SUP																					
LEANE DOMERGUE	26/05/2025	22		2	LATTE	2,00 €	4,00 €																						
BENOIT COUX	26/05/2025	22		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
KEAN DEQUEANT	26/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	26/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	26/05/2025	22		1	FLAT WHITE	2,00 €	2,00 €																						
VALERIE HAMEAU	26/05/2025	22		2	CHICORYCCINO	2,00 €	4,00 €	L																					
SUIVI CONSO GRATUITE	26/05/2025	22		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/05/2025	22		27	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/05/2025	22		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/05/2025	22		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
EMMA CADIER	27/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
SHUYAO ZHANG	27/05/2025	22		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
MATTHIEU CROUZET	27/05/2025	22		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €																						
ELENA PERROUIN	27/05/2025	22		1	AEROCANO	1,50 €	1,50 €																						
BENJAMIN MERIEAU	27/05/2025	22		1	FLAT WHITE	2,00 €	2,00 €																						
JEAN CIAPA	27/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
MANON OLIVIER	27/05/2025	22		1	OURS BLANC	2,50 €	2,50 €																						
LAURE SARDELLA	27/05/2025	22		1	CHAÏ GLACE	3,50 €	3,50 €	XL																					
OUARDIA EL BONNOUHI	27/05/2025	22		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CLARA MANHES	27/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	27/05/2025	22		11	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/05/2025	22		16	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/05/2025	22		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/05/2025	22		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MELODIE TYLER	27/05/2025	22		1	CHOCOLAT GLACE	1,50 €	1,50 €	XXL																					
SARA TISSENIER	27/05/2025	22		1	LATTE	2,00 €	2,00 €																						
LAETITIA RUAULT DURAND	27/05/2025	22		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
JULIE COUSSE	27/05/2025	22		1	LATTE MACCHIATO (VANILLE)	3,00 €	3,00 €	SUP CHOC																					
LAURA ARLES	27/05/2025	22		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
SHUYAO ZHANG	27/05/2025	22		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
COLLEEN HANRIOT	27/05/2025	22		1	CHAÏ AVOINE	2,00 €	2,00 €																						
LAURE SARDELLA	27/05/2025	22		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
FADEL DIENE	27/05/2025	22		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
VALERIE ALASLUQUETAS	27/05/2025	22		2	CAPPUCCINO	1,50 €	3,00 €																						
EMMANUELLE VAN DEN STEEN	27/05/2025	22		1	AEROCANO	1,50 €	1,50 €																						
CHANTAL PERDIGAU	28/05/2025	22		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CHRISTELLE LAGAE	28/05/2025	22		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SOPHIE JAMAIN	28/05/2025	22		1	LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	28/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
CAMILLE BORDIGNON	28/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
LENA PAWELCZYK	28/05/2025	22		1	GRANOLA BOWL	4,50 €	4,50 €		lena.pawel06@gmail.com																				
SOPHIE DESBONNEZ	28/05/2025	22		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
LEANE DOMERGUE	28/05/2025	22		1	LATTE	2,00 €	2,00 €																						
CLARA MANHES	28/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	28/05/2025	22		1	LATTE MACCHIATO	2,50 €	2,50 €																						
VALERIE ALASLUQUETAS	28/05/2025	22		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	28/05/2025	22		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANAE LEFEVRE	28/05/2025	22		1	CHAÏ LATTE	2,00 €	2,00 €																						
PAULINE SARDA	28/05/2025	22		1	LATTE GLACE (VANILLE)	2,50 €	2,50 €		1 SHOT																				
LENA PAWELCZYK	28/05/2025	22		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €		lena.pawel06@gmail.com																				
PAULINE SARDA	28/05/2025	22		1	CHAÏ GLACE	3,50 €	3,50 €	XL																					
MAX LEVER	28/05/2025	22		1	CHICOREE	1,00 €	1,00 €	L																					
LAURE SARDELLA	28/05/2025	22		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
SUIVI CONSO GRATUITE	28/05/2025	22		11	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/05/2025	22		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/05/2025	22		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/05/2025	22		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHANTAL PERDIGAU	02/06/2025	23		2	CAPPUCCINO AVOINE	1,50 €	3,00 €																						
VALERIE HAMEAU	02/06/2025	23		2	LATTE MACCHIATO	2,50 €	5,00 €																						
BAPTISTE MATHUS	02/06/2025	23		1	CHICOREE	1,00 €	1,00 €	L																					
CLARA MANHES	02/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	02/06/2025	23		1	FLAT WHITE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	02/06/2025	23		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/06/2025	23		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/06/2025	23		7	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/06/2025	23		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SOPHIE JAMAIN	03/06/2025	23		1	LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	03/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	03/06/2025	23		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MANON OLIVIER	03/06/2025	23		1	OURS BLANC	2,50 €	2,50 €																						
BENJAMIN MERIEAU	03/06/2025	23		1	FLAT WHITE	2,00 €	2,00 €																						
BAPTISTE MATHUS	03/06/2025	23		1	CHICORYCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	03/06/2025	23		1	DIRTY CHAÏ LATTE	3,50 €	3,50 €	XL																					
CLARA MANHES	03/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
KEAN DEQUEANT	03/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
JEANNE ROBIN	03/06/2025	23		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
MORGANE BOUSQUET	03/06/2025	23		1	CHAÏ LATTE	2,50 €	2,50 €	L																					
BARNABE LEVARD	03/06/2025	23		1	GRANOLA BOWL	4,50 €	4,50 €																						
SUIVI CONSO GRATUITE	03/06/2025	23		25	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/06/2025	23		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/06/2025	23		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/06/2025	23		5	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MAX LEVER	03/06/2025	23		1	CHICOREE	1,00 €	1,00 €	L																					
ELENA PERROUIN	04/06/2025	23		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
MATTHIEU CROUZET	04/06/2025	23		1	MOCACCINO	2,00 €	2,00 €																						
CAMILLE BORDIGNON	04/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
PAULINE SARDA	04/06/2025	23		1	LATTE GLACE (VANILLE)	2,50 €	2,50 €		1 SHOT																				
VINCENT DEBRAY	04/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
CHANTAL PERDIGAU	04/06/2025	23		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
PAULINE PONTIS	04/06/2025	23		1	MOCACCINO	2,00 €	2,00 €																						
CLARA MANHES	04/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
PAULINE SARDA	04/06/2025	23		1	CHAÏ GLACE	3,50 €	3,50 €	XL																					
SARA TISSENIER	04/06/2025	23		1	LATTE	2,00 €	2,00 €																						
MAX LEVER	04/06/2025	23		1	CHICOREE	1,00 €	1,00 €	L																					
SUIVI CONSO GRATUITE	04/06/2025	23		12	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/06/2025	23		12	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/06/2025	23		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/06/2025	23		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VICTORIA PUYUELO	05/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
CHRISTOPHE BOUE	05/06/2025	23		2	LATTE MACCHIATO	2,50 €	5,00 €																						
MARGAUX DEROSIER	05/06/2025	23		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
ADRIEN MORQUE	05/06/2025	23		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CHANTAL PERDIGAU	05/06/2025	23		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
KEAN DEQUEANT	05/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
BENOIT COUX	05/06/2025	23		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	05/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
ANASTASIA DE SANTIS	05/06/2025	23		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CINDY HERAUD	05/06/2025	23		1	LATTE	2,00 €	2,00 €																						
FADEL DIENE	05/06/2025	23		1	TIRAMISU LATTE 	3,50 €	3,50 €	GLACE																					
SUIVI CONSO GRATUITE	05/06/2025	23		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/06/2025	23		14	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/06/2025	23		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/06/2025	23		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VICTORIA PUYUELO	06/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	06/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
SONIA BADENE	06/06/2025	23	FILIGRAN	1	DOUBLE ESPRESSO	0,50 €	0,50 €		sonia.badene@filigran.io																				
GAELLE PAPPO	06/06/2025	23		1	LATTE GLACE	3,00 €	3,00 €																						
SERGIO BELLON	06/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
CLARA MANHES	06/06/2025	23		1	CAPPUCCINO	1,50 €	1,50 €																						
BENJAMIN THEYTAZ	06/06/2025	23	FILIGRAN	2	MATCHA LATTE	2,00 €	4,00 €		benjamin.theytaz@hotmail.fr																				
BARNABE LEVARD	06/06/2025	23		1	AEROCANO	1,50 €	1,50 €																						
KEAN DEQUEANT	06/06/2025	23		1	AEROCANO	1,50 €	1,50 €																						
JOAQUIN SPRENG	06/06/2025	23		1	MOCACCINO GLACE	4,50 €	4,50 €	XXL+SHOT SUP																					
SUIVI CONSO GRATUITE	06/06/2025	23		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/06/2025	23		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/06/2025	23		16	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/06/2025	23		6	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/06/2025	24		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/06/2025	24		6	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/06/2025	24		2	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/06/2025	24		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENOIT GUINET	09/06/2025	24		1	MOCACCINO	2,00 €	2,00 €																						
VALERIE HAMEAU	09/06/2025	24		2	LATTE MACCHIATO	2,50 €	5,00 €																						
ANGELIQUE FOUIX	09/06/2025	24		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
ANGELIQUE FOUIX	09/06/2025	24		1	AEROCANO	1,50 €	1,50 €																						
VALERIE HAMEAU	09/06/2025	24		1	CHICORYCCINO	2,00 €	2,00 €	L																					
SHUYAO ZHANG	10/06/2025	24		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
MARGAUX DEROSIER	10/06/2025	24		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
EMMA CADIER	10/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	10/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	10/06/2025	24		1	CHAÏ GLACE	2,50 €	2,50 €																						
VLAD CERISIER	10/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
UGO DE LUCA	10/06/2025	24		1	CHICORYCCINO	2,00 €	2,00 €	L																					
MELODIE TYLER	10/06/2025	24		1	GRANOLA BOWL	4,50 €	4,50 €																						
VINCENT VENTALON	10/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
SERGIO BELLON	10/06/2025	24		2	CAPPUCCINO	1,50 €	3,00 €																						
JEAN CIAPA	10/06/2025	24		1	BABYCCINO	0,50 €	0,50 €																						
AGUSTINA WEBER	10/06/2025	24		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
LAURE SARDELLA	10/06/2025	24		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
BENOIT COUX	10/06/2025	24		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
BARNABE LEVARD	10/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	10/06/2025	24		12	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/06/2025	24		16	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/06/2025	24		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/06/2025	24		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SERGIO BELLON	10/06/2025	24		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
JEAN CIAPA	10/06/2025	24		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BARNABE LEVARD	10/06/2025	24		2	AEROCANO	1,50 €	3,00 €																						
VICTORIA PUYUELO	10/06/2025	24		1	LATTE GLACE	3,00 €	3,00 €																						
ABDEL HALIMI	10/06/2025	24		1	LATTE	2,00 €	2,00 €																						
SARA TISSENIER	10/06/2025	24		1	LATTE	2,00 €	2,00 €																						
MELODIE TYLER	10/06/2025	24		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
LAURE SARDELLA	10/06/2025	24		1	MOCACCINO	2,50 €	2,50 €	L																					
OUARDIA EL BONNOUHI	10/06/2025	24		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
CELINE PRATX	10/06/2025	24		1	LATTE	2,00 €	2,00 €																						
CHRISTELLE LAGAE	11/06/2025	24		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
MOHAMED ZAAROUR	11/06/2025	24		1	AFFOGATO	2,50 €	2,50 €																						
BENOIT COUX	11/06/2025	24		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
CAMILLE BORDIGNON	11/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	11/06/2025	24		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
EMMA CADIER	11/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
PAULINE PONTIS	11/06/2025	24		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
LAURE SARDELLA	11/06/2025	24		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
ANGELIQUE FOUIX	11/06/2025	24		1	MOCACCINO GLACE	4,50 €	4,50 €	XXL+SHOT  SUP																					
SARA TISSENIER	11/06/2025	24		1	LATTE	2,00 €	2,00 €																						
LAURE SARDELLA	11/06/2025	24		1	CHAÏ GLACE	2,50 €	2,50 €																						
SHUYAO ZHANG	11/06/2025	24		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
ANGELIQUE FOUIX	11/06/2025	24		1	CAFE FRAPPE	1,50 €	1,50 €																						
PAULINE PONTIS	11/06/2025	24		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
ANAE LEFEVRE	11/06/2025	24		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
CHERIF MILI	11/06/2025	24		2	CAFE FRAPPE	1,50 €	3,00 €																						
SUIVI CONSO GRATUITE	11/06/2025	24		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/06/2025	24		23	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/06/2025	24		19	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/06/2025	24		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHRISTELLE LAGAE	12/06/2025	24		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SHUYAO ZHANG	12/06/2025	24		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
ESTEVE PINYOL	12/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	12/06/2025	24		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
EMMA CADIER	12/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	12/06/2025	24		1	DIRTY CHAÏ LATTE	3,50 €	3,50 €	GLACE XL																					
JEAN CIAPA	12/06/2025	24		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ESTELLE LAVILLE	12/06/2025	24		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
ALEXIS BASSET	12/06/2025	24		1	MOCACCINO	2,00 €	2,00 €																						
FADEL DIENE	12/06/2025	24		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
IMENE THAMRI	12/06/2025	24		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SARA TISSENIER	12/06/2025	24		1	LATTE	2,00 €	2,00 €																						
CLAIRE BELLOC	12/06/2025	24		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
EMMANUELLE VAN DEN STEEN	12/06/2025	24		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	12/06/2025	24		12	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/06/2025	24		16	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/06/2025	24		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/06/2025	24		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
DORIAN CARDOSO	13/06/2025	24		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ELENA PERROUIN	13/06/2025	24		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ANGELIQUE FOUIX	13/06/2025	24		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
VALERIE HAMEAU	13/06/2025	24		2	LATTE MACCHIATO	2,50 €	5,00 €																						
COLLEEN HANRIOT	13/06/2025	24		1	CHAÏ LATTE	2,00 €	2,00 €																						
ANAE LEFEVRE	13/06/2025	24		1	CHAÏ GLACE	2,50 €	2,50 €																						
SHUYAO ZHANG	13/06/2025	24		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
AUDE PIERRE	13/06/2025	24		1	CHICOREE	0,50 €	0,50 €																						
VALERIE ALASLUQUETAS	13/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTEVE PINYOL	13/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	13/06/2025	24		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	13/06/2025	24		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/06/2025	24		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/06/2025	24		2	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/06/2025	24		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MARION RATIER	16/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
LOUIS JARDIN	16/06/2025	25		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
BARNABE LEVARD	16/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
BENOIT COUX	16/06/2025	25		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
CLEYDYR BEZERRA	16/06/2025	25		1	LATTE	2,00 €	2,00 €		cleydyr.bezerradealbuquerque@elastic.co																				
SUIVI CONSO GRATUITE	16/06/2025	25		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/06/2025	25		11	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/06/2025	25		6	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/06/2025	25		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VALERIE HAMEAU	16/06/2025	25		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ESTELLE LAVILLE	16/06/2025	25		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MANON OLIVIER	16/06/2025	25		1	OURS BLANC	2,50 €	2,50 €																						
BENJAMIN MERIEAU	16/06/2025	25		1	CHICORYCCINO	1,50 €	1,50 €																						
CHERIF MILI	16/06/2025	25		1	CAFE FRAPPE	1,50 €	1,50 €																						
MOUAD BELGHITI	17/06/2025	25		1	LATTE MACCHIATO	2,50 €	2,50 €																						
VICTORIA PUYUELO	17/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	17/06/2025	25		1	AEROCANO	1,50 €	1,50 €																						
EMMA CADIER	17/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	17/06/2025	25		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
MELODIE TYLER	17/06/2025	25		1	MATCHA LATTE	2,00 €	2,00 €																						
MELODIE TYLER	17/06/2025	25		1	GRANOLA BOWL	4,50 €	4,50 €																						
SHUYAO ZHANG	17/06/2025	25		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
ANAE LEFEVRE	17/06/2025	25		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
MARGAUX DEROSIER	17/06/2025	25		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
OUARDIA EL BONNOUHI	17/06/2025	25		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BAPTISTE MATHUS	17/06/2025	25		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
ELENA PERROUIN	17/06/2025	25		1	CAFE FRAPPE	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	17/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTELLE LAVILLE	17/06/2025	25		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MANON OLIVIER	17/06/2025	25		1	OURS BLANC	2,50 €	2,50 €																						
JEAN CIAPA	17/06/2025	25		1	CHICORYCCINO	2,00 €	2,00 €	L																					
LAURE SARDELLA	17/06/2025	25		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €																						
CINDY HERAUD	17/06/2025	25		1	WHITE MATCHA LATTE	3,00 €	3,00 €	GLACE																					
NATHALIE GRENET	17/06/2025	25		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	17/06/2025	25		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/06/2025	25		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/06/2025	25		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/06/2025	25		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
PAULINE SPINAZZE	17/06/2025	25		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
BAPTISTE MATHUS	17/06/2025	25		1	MOCACCINO	2,00 €	2,00 €																						
SARA TISSENIER	17/06/2025	25		1	LATTE	2,00 €	2,00 €																						
LAETITIA RUAULT DURAND	17/06/2025	25		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €		1 SHOT																				
JEANNE ROBIN	17/06/2025	25		1	LATTE GLACE	3,00 €	3,00 €																						
GREGOIRE CORBIERE	17/06/2025	25		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
MOHAMED ZAAROUR	18/06/2025	25		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
VINCENT DEBRAY	18/06/2025	25	KONBOI.ONE	1	CAPPUCCINO	1,50 €	1,50 €																						
CAMILLE BORDIGNON	18/06/2025	25	KONBOI.ONE	1	CAPPUCCINO	1,50 €	1,50 €																						
CHANTAL PERDIGAU	18/06/2025	25		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SOPHIE JAMAIN	18/06/2025	25		1	LATTE	2,00 €	2,00 €																						
EMMA CADIER	18/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	18/06/2025	25		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
ERIC GUIN	18/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
ELENA PERROUIN	18/06/2025	25		1	CAFE FRAPPE	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	18/06/2025	25		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CLARA MANHES	18/06/2025	25	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	18/06/2025	25	YUKAN	1	CAPPUCCINO	1,50 €	1,50 €																						
JOAQUIN SPRENG	18/06/2025	25		1	MOCACCINO	3,50 €	3,50 €	XXL																					
SOLENE ROSSARD	18/06/2025	25	TECHNIA	1	LATTE GLACE	3,00 €	3,00 €																						
LAURE SARDELLA	18/06/2025	25	TECHNIA	1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
BAPTISTE MATHUS	18/06/2025	25		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
JULIEN COUTURIER	18/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
GUILLAUME GRANDPRE	18/06/2025	25	TECHNIA	1	CHICORYCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	18/06/2025	25		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/06/2025	25		18	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/06/2025	25		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/06/2025	25		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SARA TISSENIER	18/06/2025	25		1	LATTE	2,00 €	2,00 €																						
SHUYAO ZHANG	18/06/2025	25		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
MAX LEVER	18/06/2025	25		1	CHICOREE	1,00 €	1,00 €	L																					
ADRIEN GRUSSE	18/06/2025	25		1	CAPPUCCINO	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
THIBAULT SICOURMAT	18/06/2025	25		1	STRAWBERRY MATCHA LATTE	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
CEDRIC BOUCHE	19/06/2025	25		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
PASCALE BOUNHENG	19/06/2025	25		1	DOUBLE ESPRESSO	0,50 €	0,50 €		pbounheng@gmail.com																				
CHANTAL PERDIGAU	19/06/2025	25		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BAPTISTE MATHUS	19/06/2025	25		1	CHICOREE	0,50 €	0,50 €																						
VICTORIA PUYUELO	19/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
ELENA PERROUIN	19/06/2025	25		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
ANAE LEFEVRE	19/06/2025	25		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
SHUYAO ZHANG	19/06/2025	25		1	LATTE GLACE (VANILLE)	3,00 €	3,00 €																						
SARAH VIGUIE	19/06/2025	25		1	LATTE GLACE	3,00 €	3,00 €																						
MANON OLIVIER	19/06/2025	25		1	OURS BLANC	2,50 €	2,50 €																						
NOEMIE CALVET	19/06/2025	25		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL	noemie.calvet@numbr.co																				
JEAN CIAPA	19/06/2025	25		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
JOAQUIN SPRENG	19/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
BENOIT COUX	19/06/2025	25		1	AEROCANO	1,50 €	1,50 €																						
BENOIT COUX	19/06/2025	25		2	LATTE GLACE VIETNAMIEN	3,00 €	6,00 €																						
PHILIPPE LANDES	19/06/2025	25		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
ANASTASIA DE SANTIS	19/06/2025	25		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
CLARA MANHES	19/06/2025	25		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTELLE LAVILLE	19/06/2025	25		1	CHAÏ GLACE	2,50 €	2,50 €																						
MANON OLIVIER	19/06/2025	25		1	OURS BLANC	3,00 €	3,00 €	GLACE																					
ELODIE ALVES	19/06/2025	25		1	LATTE GLACE VIETNAMIEN	2,50 €	2,50 €	1 SHOT																					
SUIVI CONSO GRATUITE	19/06/2025	25		9	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/06/2025	25		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/06/2025	25		17	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/06/2025	25		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
PAULINE SARDA	20/06/2025	25		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €	1SHOT																					
COLINE DACLIN	20/06/2025	25		1	DIRTY CHAÏ LATTE	4,50 €	4,50 €	GLACE+XXL																					
MATTHIEU CROUZET	20/06/2025	25		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €																						
HELENE FABRE	20/06/2025	25		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
ELENA PERROUIN	20/06/2025	25		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ARNAUD THOMAS-SERVAIS	20/06/2025	25		1	CAFE FRAPPE	1,50 €	1,50 €																						
CHARLOTTE PLAYOUST	20/06/2025	25		1	CAFE FRAPPE	1,50 €	1,50 €																						
SHUYAO ZHANG	20/06/2025	25		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	20/06/2025	25		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/06/2025	25		14	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/06/2025	25		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/06/2025	25		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
JEANNE ROBIN	23/06/2025	26		1	LATTE GLACE	3,00 €	3,00 €																						
BARNABE LEVARD	23/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
ELENA PERROUIN	23/06/2025	26		1	AEROCANO	1,50 €	1,50 €																						
ELENA PERROUIN	23/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
ANGELIQUE FOUIX	23/06/2025	26		1	MOCACCINO GLACE	4,50 €	4,50 €	XXL+SHOT SUP																					
VALERIE HAMEAU	23/06/2025	26		2	LATTE MACCHIATO	2,50 €	5,00 €																						
ELODIE ALVES	23/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MELODIE TYLER	23/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
CELINE LASBATX	23/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
VALERIE HAMEAU	23/06/2025	26		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ARNAUD THOMAS-SERVAIS	23/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
JULIE DENAT	23/06/2025	26		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
VALERIE ALASLUQUETAS	23/06/2025	26		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
REBECCA RAVOALA	23/06/2025	26		1	CHICORYCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	23/06/2025	26		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/06/2025	26		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/06/2025	26		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/06/2025	26		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENOIT COUX	24/06/2025	26		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
SHUYAO ZHANG	24/06/2025	26		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
ADRIEN MORQUE	24/06/2025	26		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
NATHALIE GRENET	24/06/2025	26		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
JEAN CIAPA	24/06/2025	26		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €																						
ESTELLE LAVILLE	24/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
VIRGINIE DEL RIEU	24/06/2025	26		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
MANON OLIVIER	24/06/2025	26		1	OURS BLANC	3,00 €	3,00 €	GLACE																					
CLAIRE BELLOC	24/06/2025	26		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
AMELIE BIMONT	24/06/2025	26		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
CHRISTELLE LAGAE	24/06/2025	26		1	DOUBLE ESPRESSO	3,50 €	3,50 €																						
MATTHEW WALKER	24/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
ERIC GUIN	24/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	24/06/2025	26		1	MATCHA LATTE GLACE	4,00 €	4,00 €	XXL																					
VIRGINIE DEL RIEU	24/06/2025	26		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
LAETITIA RUAULT DURAND	24/06/2025	26		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €		(1SHOT)																				
ESTELLE LAVILLE	24/06/2025	26		1	CHAÏ GLACE	2,50 €	2,50 €																						
COLINE DACLIN	24/06/2025	26		1	AEROCANO	1,50 €	1,50 €																						
ARNAUD THOMAS-SERVAIS	24/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
LAURE SARDELLA	24/06/2025	26		1	CHAÏ GLACE	2,50 €	2,50 €																						
SOLENE ROSSARD	24/06/2025	26		1	LATTE GLACE	3,00 €	3,00 €																						
MAX LEVER	24/06/2025	26		1	CHICOREE	1,00 €	1,00 €	L																					
JEROME ALVES	24/06/2025	26		1	AEROCANO	1,50 €	1,50 €																						
EMMANUELLE VAN DEN STEEN	24/06/2025	26		1	AEROCANO	0,00 €	0,00 €		OFFERT PAR ELSA																				
SUIVI CONSO GRATUITE	24/06/2025	26		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	24/06/2025	26		20	allongé	1,00 €	20,00 €																						
SUIVI CONSO GRATUITE	24/06/2025	26		16	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	24/06/2025	26		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
ELENA PERROUIN	25/06/2025	26		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
JEANNE ROBIN	25/06/2025	26		1	LATTE GLACE	3,00 €	3,00 €																						
MOHAMED ZAAROUR	25/06/2025	26		1	AFFOGATO	2,50 €	2,50 €																						
MOUAD BELGHITI	25/06/2025	26		1	MOCACCINO	1,50 €	1,50 €																						
CAMILLE BORDIGNON	25/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
VINCENT DEBRAY	25/06/2025	26		1	MOCACCINO	2,00 €	2,00 €																						
BAPTISTE MATHUS	25/06/2025	26		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
AGUSTINA WEBER	25/06/2025	26		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
PAULINE SARDA	25/06/2025	26		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €		1SHOT																				
PAULINE PONTIS	25/06/2025	26		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
ADRIEN MORQUE	25/06/2025	26		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
MATTHEW WALKER	25/06/2025	26		1	LATTE GLACE	3,00 €	3,00 €																						
LAURE SARDELLA	25/06/2025	26		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
VALERIE HAMEAU	25/06/2025	26		2	LATTE MACCHIATO	2,50 €	5,00 €																						
ANGELIQUE FOUIX	25/06/2025	26		1	AEROCANO	1,50 €	1,50 €																						
LAURE SARDELLA	25/06/2025	26		1	MATCHA SODA	2,50 €	2,50 €																						
BAPTISTE MATHUS	25/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
PAULINE PONTIS	25/06/2025	26		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
ELENA PERROUIN	25/06/2025	26		1	AFFOGATO	2,50 €	2,50 €																						
OUARDIA EL BONNOUHI	25/06/2025	26		1	AFFOGATO	2,50 €	2,50 €																						
PAULINE SARDA	25/06/2025	26		1	CHAÏ GLACE	2,50 €	2,50 €																						
QUENTIN LE GUILLERMIC	25/06/2025	26		1	LATTE GLACE	3,50 €	3,50 €	XXL	quentin.leguillermic@sage.com																				
SHUYAO ZHANG	25/06/2025	26		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
VALERIE HAMEAU	25/06/2025	26		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ANGELIQUE FOUIX	25/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
FLORIAN DAVASSE	25/06/2025	26		1	AFFOGATO	2,50 €	2,50 €																						
PHILIPPE LANDES	25/06/2025	26		1	LATTE GLACE (VANILLE)	3,50 €	3,50 €	XXL																					
SARA TISSENIER	25/06/2025	26		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	25/06/2025	26		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/06/2025	26		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/06/2025	26		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/06/2025	26		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MATTHIEU CROUZET	26/06/2025	26		1	DOUBLE ESPRESSO	0,50 €	0,50 €		boss																				
BENOIT COUX	26/06/2025	26		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
ESTELLE LAVILLE	26/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
BENJAMIN MERIEAU	26/06/2025	26		1	FLAT WHITE	2,00 €	2,00 €																						
JEAN CIAPA	26/06/2025	26		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ERIC GUIN	26/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
GREGORY ESTRADE	26/06/2025	26		1	LATTE	2,00 €	2,00 €																						
LAETITIA RUAULT DURAND	26/06/2025	26		1	LATTE GLACE	2,50 €	2,50 €		1SHOT																				
MELODIE TYLER	26/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
ALEXIS BASSET	26/06/2025	26		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ANASTASIA DE SANTIS	26/06/2025	26		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
ANTHONY FELIN	26/06/2025	26		1	CAFE FRAPPE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	26/06/2025	26		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/06/2025	26		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/06/2025	26		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/06/2025	26		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MATTHIEU CROUZET	27/06/2025	26		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
OUARDIA EL BONNOUHI	27/06/2025	26		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	27/06/2025	26		1	MOCACCINO GLACE	4,00 €	4,00 €	XL+1SHOT																					
VALERIE HAMEAU	27/06/2025	26		2	LATTE MACCHIATO	2,50 €	5,00 €																						
VICTORIA PUYUELO	27/06/2025	26		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	27/06/2025	26		2	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/06/2025	26		8	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/06/2025	26		6	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/06/2025	26		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VALERIE HAMEAU	27/06/2025	26		1	CHICORYCCINO	2,00 €	2,00 €	L																					
CELINE PRATX	27/06/2025	26		1	LATTE GLACE	3,00 €	3,00 €																						
PHILIPPE LANDES	27/06/2025	26		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
ANAEL MEGNA	27/06/2025	26		1	CHOCOLAT GLACE	1,50 €	1,50 €	XL																					
FRANCOIS HELLOCO	30/06/2025	27		1	CAFE FRAPPE	1,50 €	1,50 €																						
EMMA CADIER	30/06/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	30/06/2025	27		3	LATTE MACCHIATO	2,50 €	7,50 €																						
BENOIT COUX	30/06/2025	27		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
MORGANE BOUSQUET	30/06/2025	27		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
ESTELLE LAVILLE	30/06/2025	27		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MANON OLIVIER	30/06/2025	27		1	OURS BLANC	3,00 €	3,00 €	GLACE																					
ANGELIQUE FOUIX	30/06/2025	27		2	CAFE FRAPPE	1,50 €	3,00 €																						
KEAN DEQUEANT	30/06/2025	27		2	AEROCANO	1,50 €	3,00 €																						
ERIC GUIN	30/06/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	30/06/2025	27		1	CHICORYCCINO	3,50 €	3,50 €	GLACE+XXL																					
ANGELIQUE FOUIX	30/06/2025	27		1	CHICORYCCINO	2,00 €	2,00 €	GLACE																					
KENZA BERRADA	30/06/2025	27		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
SUIVI CONSO GRATUITE	30/06/2025	27		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/06/2025	27		24	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/06/2025	27		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/06/2025	27		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
NATHALIE GRENET	01/07/2025	27		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CELINE LASBATX	01/07/2025	27		1	LATTE GLACE	3,00 €	3,00 €																						
BENOIT COUX	01/07/2025	27		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
BARNABE LEVARD	01/07/2025	27		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
OUARDIA EL BONNOUHI	01/07/2025	27		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ESTELLE LAVILLE	01/07/2025	27		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MANON OLIVIER	01/07/2025	27		1	OURS BLANC	2,50 €	2,50 €																						
UGO DE LUCA	01/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	01/07/2025	27		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
NOEMIE CALVET	01/07/2025	27		1	GRANOLA BOWL	4,50 €	4,50 €																						
NOEMIE CALVET	01/07/2025	27		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
LAURE SARDELLA	01/07/2025	27		1	GRANOLA BOWL	4,50 €	4,50 €																						
LAURE SARDELLA	01/07/2025	27		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
KEAN DEQUEANT	01/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
ANAEL MEGNA	01/07/2025	27		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
MATTHEW WALKER	01/07/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	01/07/2025	27		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/07/2025	27		11	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/07/2025	27		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/07/2025	27		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
LAETITIA RUAULT DURAND	01/07/2025	27		1	LATTE GLACE (CARAMEL)	2,50 €	2,50 €		1SHOT																				
UGO DE LUCA	01/07/2025	27		1	LATTE GLACE	3,00 €	3,00 €	XXL																					
NATHALIE GRENET	01/07/2025	27		1	CAFE FRAPPE	1,50 €	1,50 €																						
LOANE CARRASSUS	01/07/2025	27		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
JEAN CIAPA	01/07/2025	27		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
ORANE TREHET	01/07/2025	27		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
JEAN-MARC D\'ANDRIA	02/07/2025	27		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
VINCENT DEBRAY	02/07/2025	27		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
CAMILLE BORDIGNON	02/07/2025	27		1	MOCACCINO	2,00 €	2,00 €																						
MARGAUX DEROSIER	02/07/2025	27		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
VICTORIA PUYUELO	02/07/2025	27		2	CAPPUCCINO	1,50 €	3,00 €																						
ANGELIQUE FOUIX	02/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
VASCO COMPAIN	02/07/2025	27		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
MELANIE ALAUX	02/07/2025	27		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
ERIC GUIN	02/07/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
CINDY HERAUD	02/07/2025	27		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
LAURE SARDELLA	02/07/2025	27		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
ANAE LEFEVRE	02/07/2025	27		1	CHAÏ LATTE	2,00 €	2,00 €																						
COLLEEN HANRIOT	02/07/2025	27		1	CHAÏ LATTE	2,00 €	2,00 €																						
VALERIE HAMEAU	02/07/2025	27		2	CHICORYCCINO	2,00 €	4,00 €	L																					
LAURE SARDELLA	02/07/2025	27		1	CHAÏ LATTE	2,00 €	2,00 €																						
UGO DE LUCA	02/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
ELENA PERROUIN	02/07/2025	27		1	CAFE FRAPPE	1,50 €	1,50 €																						
SHUYAO ZHANG	02/07/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
NICOLAS TEROL	02/07/2025	27		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
SUIVI CONSO GRATUITE	02/07/2025	27		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/07/2025	27		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/07/2025	27		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/07/2025	27		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						';
    }

}
