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
               // $this->output->writeln(sprintf('<debug>Tokens = [%s]</debug>', print_r($tokens, true)));

                $addon_comment = isset($tokens[self::FIELD_COMMENT]) ? trim($tokens[self::FIELD_COMMENT]) : null;

                $quantity = $tokens[self::FIELD_QUANTITY];

                if (in_array($addon_comment, array('Offert SH', '(offert) JOURNEE D\'ESSAI', '(OFFERT)PRIVAT. OUGANDA'))) {
                    $this->output->writeln('Offert / ignoré facturation');
                } else {
                    if ($quantity > 0) {
                        $price = $tokens[self::FIELD_TOTAL_PRICE];
                        //$this->output->writeln(sprintf('<debug>Price before = [%s]</debug>', $price));
                        $price = preg_replace('/^([0-9]+)(:?,([0-9]+))? .*$/', '$1.$3', trim($price)) . '00';
                        //$this->output->writeln(sprintf('<debug>Price after = [%s]</debug>', $price));

                        $price = $price / $quantity;
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

                                $occurs_at = preg_replace('|^([0-9]{2})/([0-9]{2})/([0-9]{4})$|', '$3-$2-$1', $tokens[self::FIELD_DATE]);
                                $product = \Illuminate\Support\Str::slug($tokens[self::FIELD_PRODUCT]);

                                $product_price = $this->getProductPricing($product);
                                if (false === $product_price) {
                                    $product_price = 0;
                                    $this->output->writeln(sprintf("<error>Produit inconnu : [%s]</error>", $product));
                                    return false;
                                }

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
            case 'XAVIER MEUNIER':
                return 7541;
            case 'LOUIS JARDIN':
                return 7422;
            case 'GAELLE PAPPO':
                return 7367;
            case 'JOANNA CLOSA':
                return 7114;
            case 'SHUYAO ZHANG':
                return 7433;
            case 'SERGIO BELLON':
                return 7414;
            case 'OLIVIA SIGNOUREL':
                return 7542;
            case 'LENA PAWELCZYK':
                return 7543;
//            case 'SOPHIE BRUNET': return null; FAH ?
            case 'CELINE PRATX':
                return 7343;
            case 'VINCENT VENTALON':
                return 5151;
            case 'BENOIT GUINET':
                return 6808;
            case 'BENJAMIN THEYTAZ':
                return 7521;
            case 'PAULINE PONTIS':
                return 7455;
            case 'CLEYDYR BEZERRA':
                return 7544;
            case 'NOEMIE CALVET':
                return 7545;
//            case 'CEDRIC SIGNE MBE': return null;
            case 'QUENTIN LE GUILLERMIC':
                return 7546;
            case 'FLORIAN DAVASSE':
                return 5738;
            case 'JEROME ALVES':
                return 1399;
            case 'REBECCA RAVOALA':
                return 7478;
            case 'CHARLOTTE PLAYOUST':
                return 7358;
            case 'ESTELLE LAVILLE':
                return 7435;
            case 'GUILLAUME GRANDPRE':
                return 6071;
            case 'MELANIE ALAUX':
                return 7547;
            case 'LOANE CARRASSUS':
                return 7532;
            case 'KENZA BERRADA':
                return 7534;
            case 'NICOLAS TEROL':
                return 7493;
            case 'EMMA TEYSSANDIER':
                return 5994;
            case 'ELLA ROGER':
                return 7551;
            case 'THOMAS VERRIER':
                return 7454;
            case 'SACHA SYENCHUK': return 7186;
            case 'JEAN VERRONS': return 7585;
            case 'AURORE VIE': return 7109;
            case 'KATY LOMBA': return 5762;
            case 'RAFAEL PANETTA': return 7696;
            case 'BALKIS EL OUAFI': return 5102;
            case 'AMANDINE HEYERE': return 6048;
            case 'KARIN ORTIZ': return 4964;
            case 'JEAN-MICHEL MATHIEU': return 4269;
            case 'BAPTISTE LANSAC': return 7697;
            case 'YANN SCHLOSSER': return 7610;
            case 'GUILLAUME RAVERAT': return 715;
            case 'VALENTINE DEMANGE': return 7568;
            case 'NICOLAS MENGIN': return 1369;
            case 'CASSY BERNARD': return 7612;
            case 'FLORIAN CORGNOU': return 5875;
            case 'GIORGIO MAZZA': return 7145;
            case 'YANNICK BOURENANE': return 7469;
            case 'ANNICK MIQUEL': return 7567;
            case 'GIULIA BARINA': return 3430;
            case 'YANN LASTAPIS': return 7631;
            case 'ARNAUD LE BIHAN': return 246;
            case 'VALERIE ALONSO': return 7616;
            case 'LAETITIA GOMEZ': return 7396;
            case 'SERVANE RIANT': return 7561;
            case 'GUILLAUME BARILLET': return 6072;
            case 'MARIELLE SCHNEIDER': return 7636;
            case 'MARINE SEPET': return 5408;
            case 'RACHEL AMALVY': return 7659;

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
            'Latte sirop érable' => 2.5,// ?
            'Latte pistache' => 2.5,// ?
            'Matcha latte jasmin' => 3.0,// ?
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
        return 'SUIVI CONSO GRATUITE	03/07/2025	27		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/07/2025	27		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/07/2025	27		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/07/2025	27		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MORGANE BOUSQUET	03/07/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	03/07/2025	27		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
IMENE THAMRI	03/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
CHRISTELLE LAGAE	03/07/2025	27		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
BAPTISTE MATHUS	03/07/2025	27		1	AEROCANO	1,50 €	1,50 €																						
MANON OLIVIER	03/07/2025	27		1	OURS BLANC	2,50 €	2,50 €																						
ESTELLE LAVILLE	03/07/2025	27		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
UGO DE LUCA	03/07/2025	27		1	CHAÏ GLACE	4,00 €	4,00 €	XXL																					
BENOIT COUX	03/07/2025	27		1	DOUBLE MACCHIATO/NOISETTE	1,50 €	1,50 €																						
ADRIEN MORQUE	03/07/2025	27		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VLAD CERISIER	03/07/2025	27		1	CAPPUCCINO	1,50 €	1,50 €																						
CEDRIC BOUCHE	03/07/2025	27		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
VIRGINIE DEL RIEU	03/07/2025	27		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
NATHALIE GRENET	03/07/2025	27		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SARAH VIGUIE	03/07/2025	27		1	LATTE GLACE	3,00 €	3,00 €																						
CLAIRE BELLOC	03/07/2025	27		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
FADEL DIENE	03/07/2025	27		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
ESTEVE PINYOL	09/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
EMMA TEYSSANDIER	09/07/2025	28		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
EMMA TEYSSANDIER	09/07/2025	28		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
ELLA ROGER	09/07/2025	28		1	LATTE GLACE	3,00 €	3,00 €		ella.roger@brevo.com																				
SEBASTIEN FLOCHLAY	09/07/2025	28		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
VINCENT DEBRAY	09/07/2025	28		1	LATTE	2,00 €	2,00 €																						
THOMAS VERRIER	09/07/2025	28		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
MATTHEW WALKER	09/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	09/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
CLARISSE LOU	09/07/2025	28		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
OUARDIA EL BONNOUHI	09/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	09/07/2025	28		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/07/2025	28		13	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/07/2025	28		5	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/07/2025	28		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
NOEMIE CALVET	10/07/2025	28		1	GRANOLA BOWL	4,50 €	4,50 €																						
SHUYAO ZHANG	10/07/2025	28		1	MATCHA LATTE	2,00 €	2,00 €																						
KATY LOMBA	10/07/2025	28		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
MARION RATIER	10/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
EMMA CADIER	10/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	10/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ADRIEN MORQUE	10/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MATTHIEU CROUZET	10/07/2025	28		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €																						
BARNABE LEVARD	10/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ERIC GUIN	10/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	10/07/2025	28		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/07/2025	28		11	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/07/2025	28		7	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/07/2025	28		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VASCO COMPAIN	11/07/2025	28		1	OURS BLANC	2,50 €	2,50 €																						
BARNABE LEVARD	11/07/2025	28		1	CAPPUCCINO GLACE	2,00 €	2,00 €																						
VLAD CERISIER	11/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VICTORIA PUYUELO	11/07/2025	28		1	MATCHA LATTE	2,00 €	2,00 €																						
OUARDIA EL BONNOUHI	11/07/2025	28		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MATTHEW WALKER	11/07/2025	28		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	11/07/2025	28		1	AEROCANO	1,50 €	1,50 €																						
LOANE CARRASSUS	11/07/2025	28		1	MOCACCINO	2,00 €	2,00 €																						
ANGELIQUE FOUIX	11/07/2025	28		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	11/07/2025	28		12	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/07/2025	28		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/07/2025	28		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/07/2025	28		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/07/2025	29		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/07/2025	29		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/07/2025	29		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/07/2025	29		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHRISTOPHE BOUE	15/07/2025	29		1	GRANOLA BOWL	4,50 €	4,50 €																						
CHRISTOPHE BOUE	15/07/2025	29		2	LATTE MACCHIATO	2,50 €	5,00 €																						
MATTHIEU CROUZET	15/07/2025	29		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €																						
BARNABE LEVARD	15/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	15/07/2025	29		1	MATCHA LATTE	3,00 €	3,00 €	XL																					
JEAN CIAPA	15/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
MANON OLIVIER	15/07/2025	29		1	OURS BLANC	2,50 €	2,50 €																						
ORANE TREHET	15/07/2025	29		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
ERIC GUIN	15/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTEVE PINYOL	15/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
MAX LEVER	15/07/2025	29		1	CHICOREE	1,00 €	1,00 €	L																					
CLARA MANHES	15/07/2025	29		1	LATTE	2,00 €	2,00 €																						
VIRGINIE DEL RIEU	15/07/2025	29		1	MATCHA SODA	2,50 €	2,50 €																						
LAETITIA RUAULT DURAND	15/07/2025	29		1	LATTE GLACE	2,50 €	2,50 €		(1SHOT)																				
ANAE LEFEVRE	15/07/2025	29		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
COLLEEN HANRIOT	15/07/2025	29		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
ELODIE ALVES	15/07/2025	29		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
JEAN CIAPA	15/07/2025	29		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
VALERIE ALASLUQUETAS	15/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
CHRISTOPHE BOUE	15/07/2025	29		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
VINCENT DEBRAY	16/07/2025	29		1	LATTE GLACE (NOISETTE)	2,50 €	2,50 €		(1SHOT)																				
MATTHIEU CROUZET	16/07/2025	29		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €																						
MATTHEW WALKER	16/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	16/07/2025	29		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
PAULINE SARDA	16/07/2025	29		2	LATTE GLACE VIETNAMIEN	2,50 €	5,00 €		(1SHOT)																				
VLAD CERISIER	16/07/2025	29		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ERIC GUIN	16/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	16/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	16/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
KEAN DEQUEANT	16/07/2025	29		1	AEROCANO	1,50 €	1,50 €																						
MAX LEVER	16/07/2025	29		1	CHICOREE	1,00 €	1,00 €	L																					
SUIVI CONSO GRATUITE	16/07/2025	29		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/07/2025	29		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/07/2025	29		5	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/07/2025	29		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VICTORIA PUYUELO	18/07/2025	29		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	18/07/2025	29		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MELODIE TYLER	18/07/2025	29		1	GRANOLA BOWL	4,50 €	4,50 €																						
MATTHIEU CROUZET	18/07/2025	29		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €																						
AURORE VIE	18/07/2025	29		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VICTORIA PUYUELO	18/07/2025	29		1	MATCHA LATTE	2,00 €	2,00 €																						
SHUYAO ZHANG	18/07/2025	29		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	18/07/2025	29		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/07/2025	29		4	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/07/2025	29		5	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/07/2025	29		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
KEAN DEQUEANT	04/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	04/08/2025	32		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	1 SHOT SUP																					
AGUSTINA WEBER	04/08/2025	32		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
BENJAMIN THEYTAZ	04/08/2025	32		1	MATCHA LATTE	2,00 €	2,00 €																						
ANAE LEFEVRE	04/08/2025	32		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
SUIVI CONSO GRATUITE	04/08/2025	32		2	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/08/2025	32		7	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/08/2025	32		7	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/08/2025	32		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
JEAN VERRONS	05/08/2025	32		2	DOUBLE ESPRESSO	0,50 €	1,00 €																						
ELLA ROGER	05/08/2025	32		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
MARGAUX DEROSIER	05/08/2025	32		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
AGUSTINA WEBER	05/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	05/08/2025	32		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
ESTELLE LAVILLE	05/08/2025	32		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
JEAN CIAPA	05/08/2025	32		1	LATTE GLACE (CARAMEL)	3,50 €	3,50 €	XXL																					
UGO DE LUCA	05/08/2025	32		1	AEROCANO	1,50 €	1,50 €																						
KEAN DEQUEANT	05/08/2025	32		1	AEROCANO	1,50 €	1,50 €																						
BENJAMIN MERIEAU	05/08/2025	32		1	FLAT WHITE	2,00 €	2,00 €																						
JEAN VERRONS	05/08/2025	32		1	FLAT WHITE	2,00 €	2,00 €																						
MARION RATIER	05/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	05/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
LAURA ARLES	05/08/2025	32		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
LAURE SARDELLA	05/08/2025	32		1	MATCHA LATTE GLACE	3,50 €	3,50 €	XL																					
BARNABE LEVARD	05/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MATTHEW WALKER	05/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	05/08/2025	32		2	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/08/2025	32		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/08/2025	32		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/08/2025	32		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VALENTIN RENAUD	05/08/2025	32		1	MOCACCINO	2,00 €	2,00 €																						
LAURE SARDELLA	05/08/2025	32		1	CHAÏ LATTE	2,00 €	2,00 €																						
MILENE SAURAT	05/08/2025	32		1	MOCACCINO	0,00 €	0,00 €		journee essai																				
SACHA SYENCHUK	06/08/2025	32		2	CAPPUCCINO AVOINE	1,50 €	3,00 €																						
VINCENT DEBRAY	06/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTEVE PINYOL	06/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
CAMILLE BORDIGNON	06/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	06/08/2025	32		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
ANAE LEFEVRE	06/08/2025	32		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
OUARDIA EL BONNOUHI	06/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BARNABE LEVARD	06/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
KEAN DEQUEANT	06/08/2025	32		1	AEROCANO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	06/08/2025	32		1	MOCACCINO GLACE	3,50 €	3,50 €	XL																					
JULIE COUSSE	06/08/2025	32		1	MATCHA LATTE GLACE	4,00 €	4,00 €	XXL																					
SUIVI CONSO GRATUITE	06/08/2025	32		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/08/2025	32		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/08/2025	32		9	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/08/2025	32		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CLAIRE BELLOC	07/08/2025	32		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
CHRISTOPHE BOUE	07/08/2025	32		2	GRANOLA BOWL	4,50 €	9,00 €																						
CHRISTOPHE BOUE	07/08/2025	32		2	LATTE MACCHIATO	2,50 €	5,00 €																						
VIRGINIE DEL RIEU	07/08/2025	32		2	MATCHA LATTE	3,00 €	6,00 €	XL																					
ESTELLE LAVILLE	07/08/2025	32		1	CHOCOLAT GLACE	1,50 €	1,50 €	XL																					
BARNABE LEVARD	07/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VLAD CERISIER	07/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CHRISTOPHE BOUE	07/08/2025	32		1	CHAÏ GLACE	2,50 €	2,50 €																						
LAURA ARLES	07/08/2025	32		1	CHAÏ AVOINE	2,00 €	2,00 €																						
CHRISTOPHE BOUE	07/08/2025	32		1	LATTE MACCHIATO	2,50 €	2,50 €																						
JULIE COUSSE	07/08/2025	32		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
SUIVI CONSO GRATUITE	07/08/2025	32		2	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/08/2025	32		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/08/2025	32		6	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/08/2025	32		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	08/08/2025	32		1	LATTE	2,00 €	2,00 €																						
SACHA SYENCHUK	08/08/2025	32		2	CAPPUCCINO	1,50 €	3,00 €																						
JEAN-MICHEL MATHIEU	08/08/2025	32		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ADRIEN MORQUE	08/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	08/08/2025	32		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	SHOT SUP																					
ELLA ROGER	08/08/2025	32		2	CAPPUCCINO	1,50 €	3,00 €																						
KARIN ORTIZ	08/08/2025	32		1	CAPPUCCINO	1,50 €	1,50 €																						
JOHAN RITTERSHAUS	08/08/2025	32		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CINDY HERAUD	08/08/2025	32		1	CHAÏ LATTE	3,00 €	3,00 €	XL																					
AMANDINE HEYERE	08/08/2025	32		1	CHICORYCCINO	1,50 €	1,50 €																						
PHILIPPE LANDES	08/08/2025	32		1	LATTE GLACE	3,00 €	3,00 €																						
MAX LEVER	08/08/2025	32		1	CHICOREE	1,00 €	1,00 €	L																					
SUIVI CONSO GRATUITE	08/08/2025	32		0	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/08/2025	32		3	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/08/2025	32		2	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/08/2025	32		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
ADRIEN MORQUE	11/08/2025	33		1	AFFOGATO	2,50 €	2,50 €																						
KEAN DEQUEANT	11/08/2025	33		2	AEROCANO	1,50 €	3,00 €																						
SACHA SYENCHUK	11/08/2025	33		2	CAPPUCCINO AVOINE	1,50 €	3,00 €																						
MAX LEVER	11/08/2025	33		1	CHICOREE	1,00 €	1,00 €	L																					
ANGELIQUE FOUIX	11/08/2025	33		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	11/08/2025	33		9	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/08/2025	33		6	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/08/2025	33		6	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/08/2025	33		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VASCO COMPAIN	12/08/2025	33		1	OURS BLANC	2,50 €	2,50 €																						
ADRIEN MORQUE	12/08/2025	33		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
LAURE SARDELLA	12/08/2025	33		1	ESPRESSO TONIC	2,00 €	2,00 €																						
ANAEL MEGNA	12/08/2025	33		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	12/08/2025	33		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/08/2025	33		9	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/08/2025	33		5	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/08/2025	33		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SACHA SYENCHUK	13/08/2025	33		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
LAURE SARDELLA	13/08/2025	33		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
ALEXANDRE GORSKI	13/08/2025	33		1	CHOCOLAT GLACE	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
MAX LEVER	13/08/2025	33		1	CHICOREE	1,00 €	1,00 €	L																					
LAURE SARDELLA	13/08/2025	33		1	LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	13/08/2025	33		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/08/2025	33		16	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/08/2025	33		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	13/08/2025	33		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	14/08/2025	33		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ELLA ROGER	14/08/2025	33		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
KEAN DEQUEANT	14/08/2025	33		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	14/08/2025	33		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/08/2025	33		0	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/08/2025	33		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	14/08/2025	33		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	18/08/2025	34		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	18/08/2025	34		1	LATTE GLACE (NOISETTE)	3,00 €	3,00 €																						
MARION RATIER	18/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	18/08/2025	34		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SUIVI CONSO GRATUITE	18/08/2025	34		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/08/2025	34		8	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/08/2025	34		1	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/08/2025	34		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
LAURA ARLES	19/08/2025	34		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
VLAD CERISIER	19/08/2025	34		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MARGAUX DEROSIER	19/08/2025	34		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
ORANE TREHET	19/08/2025	34		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
JEANNE ROBIN	19/08/2025	34		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
KEAN DEQUEANT	19/08/2025	34		1	AEROCANO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	19/08/2025	34		2	CAPPUCCINO	1,50 €	3,00 €																						
CLARA MANHES	19/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
ELLA ROGER	19/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
BENJAMIN MERIEAU	19/08/2025	34		1	FLAT WHITE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	19/08/2025	34		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/08/2025	34		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/08/2025	34		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/08/2025	34		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BALKIS EL OUAFI	19/08/2025	34		1	LATTE GLACE	3,00 €	3,00 €																						
BENOIT COUX	19/08/2025	34		1	CAFE FRAPPE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	20/08/2025	34		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/08/2025	34		5	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/08/2025	34		17	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	20/08/2025	34		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
NATHALIE GRENET	20/08/2025	34		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	20/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	20/08/2025	34		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	XXL																					
VASCO COMPAIN	20/08/2025	34		1	OURS BLANC	2,50 €	2,50 €																						
LAURE SARDELLA	20/08/2025	34		1	MOCACCINO	3,00 €	3,00 €	XL																					
MAX LEVER	20/08/2025	34		1	CHICOREE	1,00 €	1,00 €	L																					
ANGELIQUE FOUIX	20/08/2025	34		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
LAURE SARDELLA	20/08/2025	34		1	MATCHA LATTE	2,00 €	2,00 €																						
MARGAUX DEROSIER	21/08/2025	34		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
MATTHEW WALKER	21/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
NICOLAS MENGIN	21/08/2025	34		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
MATTHIEU CROUZET	21/08/2025	34		1	LATTE GLACE (COOKIE)	3,00 €	3,00 €																						
ELLA ROGER	21/08/2025	34		1	MOCACCINO	2,00 €	2,00 €																						
VALERIE ALASLUQUETAS	21/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	21/08/2025	34		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/08/2025	34		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/08/2025	34		14	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	21/08/2025	34		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENJAMIN THEYTAZ	21/08/2025	34		1	MATCHA LATTE	2,00 €	2,00 €																						
MAX LEVER	21/08/2025	34		1	CHICOREE	1,00 €	1,00 €	L																					
BALKIS EL OUAFI	22/08/2025	34		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
MARION RATIER	22/08/2025	34		1	CAPPUCCINO	1,50 €	1,50 €																						
ANAE LEFEVRE	22/08/2025	34		1	CHAÏ LATTE	2,00 €	2,00 €																						
KARIN ORTIZ	22/08/2025	34		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	22/08/2025	34		1	LATTE MACCHIATO (NOISETTE)	3,00 €	3,00 €	XXL																					
VALENTINE DEMANGE	22/08/2025	34		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	22/08/2025	34		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/08/2025	34		1	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/08/2025	34		1	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/08/2025	34		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
EMMA CADIER	25/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	25/08/2025	35		1	LATTE MACCHIATO (NOISETTE)	3,50 €	3,50 €	XXL+SHOT SUP																					
VALERIE HAMEAU	25/08/2025	35		1	LATTE MACCHIATO	2,50 €	2,50 €																						
VALERIE HAMEAU	25/08/2025	35		2	CHICORYCCINO	3,00 €	6,00 €	XXL																					
JULIE COUSSE	25/08/2025	35		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
BARNABE LEVARD	25/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
MOUAD BELGHITI	25/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
ANGELIQUE FOUIX	25/08/2025	35		1	AEROCANO	1,50 €	1,50 €																						
MAX LEVER	25/08/2025	35		1	CHICOREE	1,00 €	1,00 €	L																					
PAULINE SPINAZZE	25/08/2025	35		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
SUIVI CONSO GRATUITE	25/08/2025	35		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/08/2025	35		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/08/2025	35		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/08/2025	35		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	26/08/2025	35		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MATTHIEU CROUZET	26/08/2025	35		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
MARGAUX DEROSIER	26/08/2025	35		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
LAURA ARLES	26/08/2025	35		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
EMMA CADIER	26/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	26/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
JEAN CIAPA	26/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
UGO DE LUCA	26/08/2025	35		1	CHICORYCCINO	3,50 €	3,50 €	XXL+GLACE																					
BENJAMIN MERIEAU	26/08/2025	35		1	FLAT WHITE	2,00 €	2,00 €																						
LAURE SARDELLA	26/08/2025	35		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
VIRGINIE DEL RIEU	26/08/2025	35		1	MATCHA LATTE AVOINE	3,00 €	3,00 €	XL																					
CLARA MANHES	26/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	26/08/2025	35		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/08/2025	35		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/08/2025	35		7	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/08/2025	35		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SARA TISSENIER	26/08/2025	35		1	LATTE	2,00 €	2,00 €																						
LAETITIA RUAULT DURAND	26/08/2025	35		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
MOHAMED ZAAROUR	27/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
SACHA SYENCHUK	27/08/2025	35		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CAMILLE BORDIGNON	27/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	27/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
AGUSTINA WEBER	27/08/2025	35		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
VALERIE ALASLUQUETAS	27/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	27/08/2025	35		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
ANGELIQUE FOUIX	27/08/2025	35		1	LATTE MACCHIATO (NOISETTE)	3,50 €	3,50 €	XXL+SHOT SUP																					
VALERIE HAMEAU	27/08/2025	35		1	LATTE MACCHIATO	3,50 €	3,50 €	XXL+SHOT SUP																					
VALERIE HAMEAU	27/08/2025	35		1	CHICORYCCINO	3,00 €	3,00 €	XXL																					
NATHALIE GRENET	27/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	27/08/2025	35		9	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/08/2025	35		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/08/2025	35		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	27/08/2025	35		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SARAH VIGUIE	28/08/2025	35		1	LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	28/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	28/08/2025	35		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	28/08/2025	35		1	MATCHA LATTE AVOINE	3,00 €	3,00 €	XL																					
UGO DE LUCA	28/08/2025	35		1	DIRTY CHAÏ LATTE	3,50 €	3,50 €	XL																					
JEAN CIAPA	28/08/2025	35		1	DIRTY CHAÏ LATTE	3,50 €	3,50 €	XL																					
MARION RATIER	28/08/2025	35		1	CHAÏ LATTE	2,00 €	2,00 €																						
ERIC GUIN	28/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
FADEL DIENE	28/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
SOLENE LAYBROS	28/08/2025	35		1	GRANOLA BOWL	4,50 €	4,50 €																						
LAETITIA RUAULT DURAND	28/08/2025	35		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
VIRGINIE DEL RIEU	28/08/2025	35		1	DIRTY CHAÏ LATTE	3,00 €	3,00 €	L																					
SARA TISSENIER	28/08/2025	35		2	LATTE	2,00 €	4,00 €																						
BARNABE LEVARD	28/08/2025	35		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
PAULINE SPINAZZE	28/08/2025	35		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	28/08/2025	35		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/08/2025	35		13	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/08/2025	35		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	28/08/2025	35		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	29/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
GUILLAUME RAVERAT	29/08/2025	35		1	THE MATCHA	2,00 €	2,00 €																						
CEDRIC BOUCHE	29/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
ANGELIQUE FOUIX	29/08/2025	35		1	LATTE MACCHIATO (NOISETTE)	3,50 €	3,50 €	XXL+SHOT SUP																					
KARIN ORTIZ	29/08/2025	35		1	MOCA	1,00 €	1,00 €																						
YANN SCHLOSSER	29/08/2025	35		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €		yann.schlosser@gmail.com																				
ADRIEN MORQUE	29/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	29/08/2025	35		1	LATTE MACCHIATO	3,50 €	3,50 €	XXL+SHOT SUP																					
ESTEVE PINYOL	29/08/2025	35		1	CAPPUCCINO	1,50 €	1,50 €																						
NATHALIE GRENET	29/08/2025	35		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CEDRIC BOUCHE	29/08/2025	35		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
BARNABE LEVARD	29/08/2025	35		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
ELODIE ALVES	29/08/2025	35		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
ANAE LEFEVRE	29/08/2025	35		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
BENJAMIN THEYTAZ	29/08/2025	35		1	MATCHA LATTE	2,00 €	2,00 €																						
VALERIE HAMEAU	29/08/2025	35		1	CHICORYCCINO	3,00 €	3,00 €	XXL																					
JULIE COUSSE	29/08/2025	35		1	MOCACCINO	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	29/08/2025	35		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/08/2025	35		13	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/08/2025	35		4	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/08/2025	35		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	01/09/2025	36		1	FLAT WHITE	2,00 €	2,00 €																						
BARNABE LEVARD	01/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BAPTISTE MATHUS	01/09/2025	36		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
VICTORIA PUYUELO	01/09/2025	36		2	CAPPUCCINO	1,50 €	3,00 €																						
ANGELIQUE FOUIX	01/09/2025	36		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
VALERIE HAMEAU	01/09/2025	36		1	LATTE MACCHIATO	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	01/09/2025	36		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/09/2025	36		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/09/2025	36		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/09/2025	36		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BAPTISTE MATHUS	01/09/2025	36		1	MOCACCINO	2,00 €	2,00 €																						
ELENA PERROUIN	02/09/2025	36		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ERIC GUIN	02/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	02/09/2025	36		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
BARNABE LEVARD	02/09/2025	36		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
ELODIE ALVES	02/09/2025	36		1	INFUSION GINGER LEMON	0,50 €	0,50 €																						
VALERIE ALASLUQUETAS	02/09/2025	36		2	CAPPUCCINO	1,50 €	3,00 €																						
PHILIPPE LANDES	02/09/2025	36		2	LATTE MACCHIATO	2,50 €	5,00 €																						
MARGAUX DEROSIER	02/09/2025	36		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
ABDEL HALIMI	02/09/2025	36		1	CAPPUCCINO AVOINE	0,00 €	0,00 €		OFFERT ELSA																				
SUIVI CONSO GRATUITE	02/09/2025	36		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/09/2025	36		25	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/09/2025	36		17	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/09/2025	36		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MAX LEVER	02/09/2025	36		1	CHICOREE	1,00 €	1,00 €	L																					
JULIE COUSSE	02/09/2025	36		1	MATCHA LATTE GLACE	4,00 €	4,00 €	XXL																					
BENJAMIN THEYTAZ	02/09/2025	36		1	MATCHA LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	02/09/2025	36		1	MATCHA LATTE	2,00 €	2,00 €																						
LAURE SARDELLA	02/09/2025	36		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
EMMA CADIER	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
THOMAS VERRIER	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
CHANTAL PERDIGAU	03/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CEDRIC BOUCHE	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	03/09/2025	36		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
VALERIE ALASLUQUETAS	03/09/2025	36		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
BARNABE LEVARD	03/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ERIC GUIN	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
BAPTISTE MATHUS	03/09/2025	36		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
ELLA ROGER	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
BAPTISTE LANSAC	03/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €		lansac.baptiste@gmail.com																				
SUIVI CONSO GRATUITE	03/09/2025	36		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/09/2025	36		21	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/09/2025	36		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/09/2025	36		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
MARGAUX DEROSIER	04/09/2025	36		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
FLORIAN CORGNOU	04/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
ADRIEN MORQUE	04/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ELLA ROGER	04/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
BAPTISTE MATHUS	04/09/2025	36		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
AGUSTINA WEBER	04/09/2025	36		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
CLAIRE BELLOC	04/09/2025	36		2	CHICORYCCINO	1,50 €	3,00 €																						
VIRGINIE DEL RIEU	04/09/2025	36		1	DIRTY CHAÏ LATTE	2,50 €	2,50 €																						
CHRISTELLE LAGAE	04/09/2025	36		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ERIC GUIN	04/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTELLE LAVILLE	04/09/2025	36		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
BAPTISTE MATHUS	04/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	04/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	04/09/2025	36		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
LAETITIA RUAULT DURAND	04/09/2025	36		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
IMENE THAMRI	04/09/2025	36		1	LATTE GLACE VIETNAMIEN	3,00 €	3,00 €																						
SARA TISSENIER	04/09/2025	36		1	LATTE	2,00 €	2,00 €																						
CINDY HERAUD	04/09/2025	36		1	LATTE	2,00 €	2,00 €																						
CLAIRE BELLOC	04/09/2025	36		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
CHRISTELLE LAGAE	04/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	04/09/2025	36		4	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/09/2025	36		23	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/09/2025	36		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	04/09/2025	36		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
KARIN ORTIZ	05/09/2025	36		1	CAPPUCCINO	1,50 €	1,50 €																						
CEDRIC BOUCHE	05/09/2025	36		1	FLAT WHITE	2,00 €	2,00 €																						
CASSY BERNARD	05/09/2025	36		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
BARNABE LEVARD	05/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ADRIEN MORQUE	05/09/2025	36		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VALENTINE DEMANGE	05/09/2025	36		1	WHITE MATCHA LATTE	2,50 €	2,50 €																						
BAPTISTE MATHUS	05/09/2025	36		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
CINDY HERAUD	05/09/2025	36		1	LATTE	2,00 €	2,00 €																						
CEDRIC BOUCHE	05/09/2025	36		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
JEREMY BELHADJ	05/09/2025	36		1	MOCACCINO	2,50 €	2,50 €	SHOT SUP																					
GIULIA BARINA	05/09/2025	36		1	CHICOREE	0,50 €	0,50 €																						
IMENE THAMRI	05/09/2025	36		1	AEROCANO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	05/09/2025	36		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/09/2025	36		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/09/2025	36		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	05/09/2025	36		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHRISTELLE LAGAE	08/09/2025	37		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CEDRIC BOUCHE	08/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE HAMEAU	08/09/2025	37		2	LATTE MACCHIATO	2,50 €	5,00 €																						
AGUSTINA WEBER	08/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CLARISSE LOU	08/09/2025	37		1	MATCHA LATTE	2,00 €	2,00 €																						
ELLA ROGER	08/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
BARNABE LEVARD	08/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
KEAN DEQUEANT	08/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
BALKIS EL OUAFI	08/09/2025	37		1	MATCHA LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	08/09/2025	37		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
JULIE COUSSE	08/09/2025	37		1	MATCHA LATTE GLACE	4,00 €	4,00 €	XL																					
SARA TISSENIER	08/09/2025	37		1	LATTE	2,00 €	2,00 €																						
VALERIE HAMEAU	08/09/2025	37		1	CHICORYCCINO	2,50 €	2,50 €	XL																					
SUIVI CONSO GRATUITE	08/09/2025	37		4	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/09/2025	37		22	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/09/2025	37		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	08/09/2025	37		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
ELENA PERROUIN	09/09/2025	37		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CEDRIC BOUCHE	09/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
UGO DE LUCA	09/09/2025	37		1	CHICORYCCINO	2,00 €	2,00 €	L																					
ESTELLE LAVILLE	09/09/2025	37		1	CHOCOLAT GLACE	0,50 €	0,50 €																						
CELINE PRATX	09/09/2025	37		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
VLAD CERISIER	09/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
AGUSTINA WEBER	09/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
VICTORIA PUYUELO	09/09/2025	37		2	CAPPUCCINO	1,50 €	3,00 €																						
ERIC GUIN	09/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
SARAH VIGUIE	09/09/2025	37		1	LATTE	2,00 €	2,00 €																						
ANNICK MIQUEL	09/09/2025	37		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
SUIVI CONSO GRATUITE	09/09/2025	37		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/09/2025	37		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/09/2025	37		20	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	09/09/2025	37		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENJAMIN THEYTAZ	09/09/2025	37		1	MATCHA LATTE	2,00 €	2,00 €																						
CINDY HERAUD	09/09/2025	37		1	LATTE	2,00 €	2,00 €																						
LAURE SARDELLA	09/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
MELODIE TYLER	09/09/2025	37		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
CASSANDRA BONNAFOUS	09/09/2025	37		1	INFUSION GINGER LEMON	0,50 €	0,50 €																						
CELINE PRATX	09/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BENJAMIN MERIEAU	09/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
IMENE THAMRI	09/09/2025	37		2	CAPPUCCINO	1,50 €	3,00 €																						
MAX LEVER	09/09/2025	37		1	CHICOREE	1,00 €	1,00 €	L																					
SARA TISSENIER	09/09/2025	37		1	LATTE	2,00 €	2,00 €																						
CEDRIC BOUCHE	10/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
EMMA CADIER	10/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
MARGAUX DEROSIER	10/09/2025	37		1	LATTE GLACE (CARAMEL)	3,00 €	3,00 €																						
MATTHIEU CROUZET	10/09/2025	37		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €																						
LAURE SARDELLA	10/09/2025	37		1	LATTE MACCHIATO (VANILLE)	2,50 €	2,50 €																						
OUARDIA EL BONNOUHI	10/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MARIELLE SCHNEIDER	10/09/2025	37		1	CHICORYCCINO	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
CEDRIC BOUCHE	10/09/2025	37		1	CHAÏ AVOINE	2,50 €	2,50 €	L																					
ANGELIQUE FOUIX	10/09/2025	37		1	LATTE	2,00 €	2,00 €																						
MARIE MARCHANDISE	10/09/2025	37		1	AEROCANO	0,00 €	0,00 €		JOURNEE D\'ESSAI																				
GIULIA BARINA	10/09/2025	37		1	CHICOREE	0,50 €	0,50 €																						
CEDRIC BOUCHE	10/09/2025	37		1	LATTE GLACE	3,00 €	3,00 €																						
LAURE SARDELLA	10/09/2025	37		1	PEACH GINGER FIZZ	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	10/09/2025	37		3	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/09/2025	37		18	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/09/2025	37		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	10/09/2025	37		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	11/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
CHRISTELLE LAGAE	11/09/2025	37		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
MARGAUX DEROSIER	11/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
FABRICE RAKOTONARIVO	11/09/2025	37		2	LATTE MACCHIATO (NOISETTE)	2,50 €	5,00 €																						
EMMA CADIER	11/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
BAPTISTE MATHUS	11/09/2025	37		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
CHRISTOPHE BOUE	11/09/2025	37		2	LATTE MACCHIATO	2,50 €	5,00 €																						
ADRIEN MORQUE	11/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	11/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
JEAN CIAPA	11/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
UGO DE LUCA	11/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
NATHALIE GRENET	11/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
AGUSTINA WEBER	11/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
UGO DE LUCA	11/09/2025	37		1	CHICORYCCINO	2,50 €	2,50 €	XL																					
JEAN CIAPA	11/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
LAETITIA RUAULT DURAND	11/09/2025	37		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
VIRGINIE DEL RIEU	11/09/2025	37		1	MATCHA LATTE AVOINE	3,50 €	3,50 €	XXL																					
CHRISTOPHE BOUE	11/09/2025	37		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
SUIVI CONSO GRATUITE	11/09/2025	37		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/09/2025	37		27	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/09/2025	37		18	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	11/09/2025	37		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
AGUSTINA WEBER	11/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
ADRIEN MORQUE	11/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
ADRIANA ROA	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
VICTORIA PUYUELO	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
BALKIS EL OUAFI	12/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
CEDRIC BOUCHE	12/09/2025	37		1	FLAT WHITE	2,00 €	2,00 €																						
YANNICK BOURENANE	12/09/2025	37		1	LATTE	2,00 €	2,00 €																						
LOUIS JARDIN	12/09/2025	37		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ADRIEN MORQUE	12/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
REBECCA RAVOALA	12/09/2025	37		1	CHICORYCCINO	1,50 €	1,50 €																						
ELLA ROGER	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
GIORGIO MAZZA	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
ESTEVE PINYOL	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	12/09/2025	37		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
ANAEL MEGNA	12/09/2025	37		1	CAPPUCCINO	1,50 €	1,50 €																						
JEAN CIAPA	12/09/2025	37		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
SUIVI CONSO GRATUITE	12/09/2025	37		5	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/09/2025	37		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/09/2025	37		8	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	12/09/2025	37		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	15/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
BARNABE LEVARD	15/09/2025	38		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VALERIE HAMEAU	15/09/2025	38		1	LATTE MACCHIATO	2,50 €	2,50 €																						
ANGELIQUE FOUIX	15/09/2025	38		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
VALERIE HAMEAU	15/09/2025	38		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
CEDRIC BOUCHE	15/09/2025	38		1	LATTE PISTACHE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	15/09/2025	38		21	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/09/2025	38		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/09/2025	38		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	15/09/2025	38		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
VALERIE ALASLUQUETAS	15/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
JEROME ALVES	15/09/2025	38		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
CEDRIC BOUCHE	16/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
BAPTISTE MATHUS	16/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
YANN LASTAPIS	16/09/2025	38		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CASSY BERNARD	16/09/2025	38		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
SARAH VIGUIE	16/09/2025	38		1	LATTE	2,00 €	2,00 €																						
MOUAD BELGHITI	16/09/2025	38		1	LATTE PISTACHE	2,50 €	2,50 €																						
NATHALIE GRENET	16/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
BENJAMIN MERIEAU	16/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
JEAN CIAPA	16/09/2025	38		1	LATTE PISTACHE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	16/09/2025	38		18	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/09/2025	38		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/09/2025	38		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	16/09/2025	38		5	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BENJAMIN MERIEAU	16/09/2025	38		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
LOUIS JARDIN	16/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	16/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
JULIE COUSSE	16/09/2025	38		1	LATTE PISTACHE	2,50 €	2,50 €																						
MORGANE BOUSQUET	17/09/2025	38		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
SACHA SYENCHUK	17/09/2025	38		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CEDRIC BOUCHE	17/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
CHANTAL PERDIGAU	17/09/2025	38		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	17/09/2025	38		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VICTORIA PUYUELO	17/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
CASSY BERNARD	17/09/2025	38		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €	XXL																					
CAMILLE BORDIGNON	17/09/2025	38		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
THOMAS VERRIER	17/09/2025	38		1	BABYCCINO	0,50 €	0,50 €																						
LAURE SARDELLA	17/09/2025	38		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	17/09/2025	38		15	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/09/2025	38		13	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/09/2025	38		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	17/09/2025	38		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
ANAEL MEGNA	17/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
VALERIE ALASLUQUETAS	17/09/2025	38		1	MATCHA LATTE GLACE	2,50 €	2,50 €																						
LAURE SARDELLA	17/09/2025	38		1	LATTE PISTACHE	2,50 €	2,50 €																						
CEDRIC BOUCHE	17/09/2025	38		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
CEDRIC BOUCHE	18/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
AMELIE BIMONT	18/09/2025	38		1	GRANOLA BOWL	4,50 €	4,50 €																						
GAELLE PAPPO	18/09/2025	38		1	LATTE GLACE	3,00 €	3,00 €																						
MATTHIEU CROUZET	18/09/2025	38		1	LATTE MACCHIATO (COOKIES)	2,50 €	2,50 €																						
JEROME ALVES	18/09/2025	38		1	MOCACCINO	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	18/09/2025	38		17	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/09/2025	38		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/09/2025	38		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	18/09/2025	38		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHERIF MILI	18/09/2025	38		3	IMMUNITY SHOT	1,00 €	3,00 €																						
CEDRIC BOUCHE	18/09/2025	38		1	LATTE GLACE	3,00 €	3,00 €																						
BAPTISTE MATHUS	19/09/2025	38		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
CHANTAL PERDIGAU	19/09/2025	38		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
CEDRIC BOUCHE	19/09/2025	38		1	FLAT WHITE	2,00 €	2,00 €																						
VICTORIA PUYUELO	19/09/2025	38		2	CAPPUCCINO	1,50 €	3,00 €																						
ADRIANA ROA	19/09/2025	38		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
OUARDIA EL BONNOUHI	19/09/2025	38		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CINDY HERAUD	19/09/2025	38		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
ADRIANA ROA	19/09/2025	38		1	CAPPUCCINO	1,50 €	1,50 €																						
CHERIF MILI	19/09/2025	38		1	IMMUNITY SHOT	1,00 €	1,00 €																						
BALKIS EL OUAFI	19/09/2025	38		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
JULIE DENAT	19/09/2025	38		1	STRAWBERRY MATCHA LATTE	3,50 €	3,50 €																						
CLEMENTINE CABROL	19/09/2025	38		1	LATTE PISTACHE	3,50 €	3,50 €	GLACE+SHOT SUP																					
SUIVI CONSO GRATUITE	19/09/2025	38		13	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/09/2025	38		9	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/09/2025	38		6	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	19/09/2025	38		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	22/09/2025	39		1	FLAT WHITE	2,00 €	2,00 €																						
VLAD CERISIER	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
LAURA ARLES	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
ANGELIQUE FOUIX	22/09/2025	39		1	LATTE MACCHIATO (NOISETTE)	2,50 €	2,50 €																						
VALERIE HAMEAU	22/09/2025	39		2	LATTE MACCHIATO	2,50 €	5,00 €																						
ANAEL MEGNA	22/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
JULIE COUSSE	22/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
MARINE SEPET	22/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
BARNABE LEVARD	22/09/2025	39		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
TRISTAN BAEUMLIN	22/09/2025	39		1	CHICOREE	0,00 €	0,00 €		SALLE DE REUNION 2PERS																				
TRISTAN BAEUMLIN	22/09/2025	39		1	MATCHA LATTE GLACE	0,00 €	0,00 €		SALLE DE REUNION 2PERS																				
CHARLOTTE PLAYOUST	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
CHERIF MILI	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
JULIE DENAT	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
VALERIE HAMEAU	22/09/2025	39		1	CHICORYCCINO	2,50 €	2,50 €	XL																					
ELLA ROGER	22/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
MARIELLE SCHNEIDER	22/09/2025	39		1	CHICOREE	0,50 €	0,50 €																						
AGUSTINA WEBER	22/09/2025	39		2	MACCHIATO/NOISETTE	1,00 €	2,00 €																						
PHILIPPE LANDES	22/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	22/09/2025	39		1	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/09/2025	39		20	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/09/2025	39		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	22/09/2025	39		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	23/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
JEANNE ROBIN	23/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
ORANE TREHET	23/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
VIRGINIE DEL RIEU	23/09/2025	39		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
SARAH VIGUIE	23/09/2025	39		1	LATTE	2,00 €	2,00 €																						
BENJAMIN MERIEAU	23/09/2025	39		1	FLAT WHITE	2,00 €	2,00 €																						
AGUSTINA WEBER	23/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
NICOLAS TEROL	23/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
CEDRIC BOUCHE	23/09/2025	39		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
LAETITIA RUAULT DURAND	23/09/2025	39		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
JEAN CIAPA	23/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
GUILLAUME BARILLET	23/09/2025	39	TECHNIA	1	CHICOREE	0,50 €	0,50 €																						
CHERIF MILI	23/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
MELODIE TYLER	23/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
JULIE COUSSE	23/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
BARNABE LEVARD	23/09/2025	39		1	MATCHA LATTE AVOINE	3,00 €	3,00 €																						
SUIVI CONSO GRATUITE	23/09/2025	39		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/09/2025	39		15	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/09/2025	39		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	23/09/2025	39		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	24/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
SADRI LASSOUED	24/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
CAMILLE BORDIGNON	24/09/2025	39		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
THOMAS VERRIER	24/09/2025	39		1	BABYCCINO	0,50 €	0,50 €																						
EMMA CADIER	24/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
VINCENT DEBRAY	24/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
GUILLAUME BARILLET	24/09/2025	39		1	CHICOREE	0,50 €	0,50 €																						
BENJAMIN THEYTAZ	24/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
CEDRIC BOUCHE	24/09/2025	39		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
VICTORIA PUYUELO	24/09/2025	39		2	CAPPUCCINO	1,50 €	3,00 €																						
OUARDIA EL BONNOUHI	24/09/2025	39		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
LAURE SARDELLA	24/09/2025	39		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
AGUSTINA WEBER	24/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
MATTHIEU CROUZET	24/09/2025	39		2	boisson de la semaine	0,50 €	1,00 €	SHOT SUP																					
ERIC GUIN	24/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
SARA TISSENIER	24/09/2025	39		1	LATTE	2,00 €	2,00 €																						
BARNABE LEVARD	24/09/2025	39		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
LAURE SARDELLA	24/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
MARIELLE SCHNEIDER	24/09/2025	39		1	CHICOREE	1,00 €	1,00 €	L																					
SUIVI CONSO GRATUITE	24/09/2025	39		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	24/09/2025	39		38	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	24/09/2025	39		12	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	24/09/2025	39		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BAPTISTE MATHUS	25/09/2025	39		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
CEDRIC BOUCHE	25/09/2025	39		1	FLAT WHITE	2,00 €	2,00 €																						
JOHAN RITTERSHAUS	25/09/2025	39		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
OUARDIA EL BONNOUHI	25/09/2025	39		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	25/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
MOUAD BELGHITI	25/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
AGUSTINA WEBER	25/09/2025	39		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
BARNABE LEVARD	25/09/2025	39		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ELLA ROGER	25/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
VIRGINIE DEL RIEU	25/09/2025	39		1	MATCHA LATTE AVOINE	3,00 €	3,00 €	XL																					
LAETITIA RUAULT DURAND	25/09/2025	39		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
JULIE COUSSE	25/09/2025	39		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
CHERIF MILI	25/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
FADEL DIENE	25/09/2025	39		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
JEAN CIAPA	25/09/2025	39		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	25/09/2025	39		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/09/2025	39		16	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/09/2025	39		17	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	25/09/2025	39		1	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	26/09/2025	39		1	FLAT WHITE	2,00 €	2,00 €																						
ADRIEN MORQUE	26/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
AGUSTINA WEBER	26/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
CLARISSE LOU	26/09/2025	39		1	boisson de la semaine	0,50 €	0,50 €	SHOT SUP																					
BAPTISTE MATHUS	26/09/2025	39		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
KARIN ORTIZ	26/09/2025	39		1	CAPPUCCINO	1,50 €	1,50 €																						
CEDRIC BOUCHE	26/09/2025	39		1	CHAÏ LATTE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	26/09/2025	39		8	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/09/2025	39		10	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/09/2025	39		10	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	26/09/2025	39		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHRISTELLE LAGAE	29/09/2025	40		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CEDRIC BOUCHE	29/09/2025	40		1	FLAT WHITE	2,00 €	2,00 €																						
VALERIE HAMEAU	29/09/2025	40		1	LATTE MACCHIATO	2,50 €	2,50 €																						
CASSY BERNARD	29/09/2025	40		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
NATHALIE GRENET	29/09/2025	40		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
ANGELIQUE FOUIX	29/09/2025	40		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	29/09/2025	40		15	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/09/2025	40		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/09/2025	40		15	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	29/09/2025	40		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
SARA TISSENIER	29/09/2025	40		1	LATTE	2,00 €	2,00 €																						
CEDRIC BOUCHE	30/09/2025	40		1	FLAT WHITE	0,00 €	0,00 €		OFFERT																				
ELENA PERROUIN	30/09/2025	40		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
VIRGINIE DEL RIEU	30/09/2025	40		1	MATCHA LATTE AVOINE	3,50 €	3,50 €	XXL																					
EMMA CADIER	30/09/2025	40		1	CAPPUCCINO	1,50 €	1,50 €																						
GUILLAUME BARILLET	30/09/2025	40		2	CHICOREE	0,50 €	1,00 €																						
BAPTISTE MATHUS	30/09/2025	40		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
BARNABE LEVARD	30/09/2025	40		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	30/09/2025	40		10	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/09/2025	40		24	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/09/2025	40		20	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	30/09/2025	40		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
LAETITIA RUAULT DURAND	30/09/2025	40		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
EMMA CADIER	30/09/2025	40		1	LATTE GLACE	3,00 €	3,00 €																						
MORGAN URIEN	30/09/2025	40		1	CHICOREE	0,50 €	0,50 €																						
LAURE SARDELLA	30/09/2025	40		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
PAULINE SPINAZZE	30/09/2025	40		1	LATTE PISTACHE	2,50 €	2,50 €																						
BARNABE LEVARD	30/09/2025	40		1	MATCHA LATTE JASMIN	3,00 €	3,00 €																						
CLARISSE LOU	01/10/2025	40		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
CEDRIC BOUCHE	01/10/2025	40		1	FLAT WHITE	2,00 €	2,00 €																						
CHRISTELLE LAGAE	01/10/2025	40		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
MATTHIEU CROUZET	01/10/2025	40		1	CHOCOLAT CHAUD	0,50 €	0,50 €	L																					
CAMILLE BORDIGNON	01/10/2025	40		1	MOCACCINO	2,00 €	2,00 €																						
VINCENT DEBRAY	01/10/2025	40		1	LATTE PISTACHE	2,50 €	2,50 €																						
CLAIRE BELLOC	01/10/2025	40		1	LATTE PISTACHE	2,50 €	2,50 €																						
MOHAMED ZAAROUR	01/10/2025	40		1	MOCA	1,00 €	1,00 €																						
MARGAUX DEROSIER	01/10/2025	40		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
ERIC GUIN	01/10/2025	40		1	CAPPUCCINO	1,50 €	1,50 €																						
JOHAN RITTERSHAUS	01/10/2025	40		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
CLAIRE BELLOC	01/10/2025	40		1	MACCHIATO/NOISETTE	1,00 €	1,00 €																						
BARNABE LEVARD	01/10/2025	40		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SERVANE RIANT	01/10/2025	40		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
LAETITIA GOMEZ	01/10/2025	40		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
SOLENE ROSSARD	01/10/2025	40		1	LATTE	2,00 €	2,00 €																						
SARA TISSENIER	01/10/2025	40		1	LATTE	2,00 €	2,00 €																						
LAURE SARDELLA	01/10/2025	40		1	LATTE PISTACHE	2,50 €	2,50 €																						
VALERIE ALONSO	01/10/2025	40		1	LATTE	2,00 €	2,00 €																						
ARNAUD LE BIHAN	01/10/2025	40		1	GRANOLA BOWL	4,50 €	4,50 €																						
SUIVI CONSO GRATUITE	01/10/2025	40		7	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/10/2025	40		11	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/10/2025	40		9	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	01/10/2025	40		0	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	02/10/2025	40		1	FLAT WHITE	2,00 €	2,00 €																						
JEAN-MICHEL MATHIEU	02/10/2025	40		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
ELENA PERROUIN	02/10/2025	40		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
BENJAMIN THEYTAZ	02/10/2025	40		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
JEAN CIAPA	02/10/2025	40		1	LATTE PISTACHE	2,50 €	2,50 €																						
UGO DE LUCA	02/10/2025	40		1	CHICORYCCINO	2,50 €	2,50 €	XL																					
VIRGINIE DEL RIEU	02/10/2025	40		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
MARIELLE SCHNEIDER	02/10/2025	40		1	CHICORYCCINO	1,50 €	1,50 €																						
UGO DE LUCA	02/10/2025	40		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
LAETITIA RUAULT DURAND	02/10/2025	40		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	02/10/2025	40		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/10/2025	40		17	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/10/2025	40		14	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	02/10/2025	40		2	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CEDRIC BOUCHE	03/10/2025	40		1	FLAT WHITE	2,00 €	2,00 €																						
VICTORIA PUYUELO	03/10/2025	40		1	CAPPUCCINO	1,50 €	1,50 €																						
CASSY BERNARD	03/10/2025	40		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
BARNABE LEVARD	03/10/2025	40		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
RACHEL AMALVY	03/10/2025	40		1	LATTE	2,00 €	2,00 €																						
BAPTISTE MATHUS	03/10/2025	40		1	FLAT WHITE	2,00 €	2,00 €																						
VICTORIA PUYUELO	03/10/2025	40		1	MATCHA LATTE	2,00 €	2,00 €																						
CINDY HERAUD	03/10/2025	40		1	LATTE	2,00 €	2,00 €																						
PAULINE SPINAZZE	03/10/2025	40		1	CHAÏ AVOINE	2,00 €	2,00 €																						
SUIVI CONSO GRATUITE	03/10/2025	40		6	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/10/2025	40		12	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/10/2025	40		13	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	03/10/2025	40		3	CHOCOLAT CHAUD	0,00 €	0,00 €																						
CHRISTELLE LAGAE	06/10/2025	41		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CEDRIC BOUCHE	06/10/2025	41		1	FLAT WHITE	2,00 €	2,00 €																						
VLAD CERISIER	06/10/2025	41		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
MARGAUX DEROSIER	06/10/2025	41		1	LATTE MACCHIATO (CARAMEL)	2,50 €	2,50 €																						
AGUSTINA WEBER	06/10/2025	41		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
ABDEL HALIMI	06/10/2025	41		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
VALERIE HAMEAU	06/10/2025	41		2	LATTE MACCHIATO	2,50 €	5,00 €																						
MARION RATIER	06/10/2025	41		1	LATTE MACCHIATO	2,50 €	2,50 €																						
MARIE-LAURE MOENS	06/10/2025	41		1	LATTE	2,00 €	2,00 €																						
ANGELIQUE FOUIX	06/10/2025	41		1	LATTE SIROP ERABLE	2,50 €	2,50 €																						
SUIVI CONSO GRATUITE	06/10/2025	41		11	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/10/2025	41		23	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/10/2025	41		11	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	06/10/2025	41		4	CHOCOLAT CHAUD	0,00 €	0,00 €																						
BARNABE LEVARD	06/10/2025	41		1	MATCHA LATTE AVOINE	2,00 €	2,00 €																						
EMMA CADIER	06/10/2025	41		1	CAPPUCCINO	1,50 €	1,50 €																						
CEDRIC BOUCHE	06/10/2025	41		1	DOUBLE ESPRESSO	0,50 €	0,50 €																						
CEDRIC BOUCHE	07/10/2025	41		1	FLAT WHITE	2,00 €	2,00 €																						
BARNABE LEVARD	07/10/2025	41		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
BAPTISTE MATHUS	07/10/2025	41		1	AMERICANO DOUBLE SHOT	0,50 €	0,50 €																						
UGO DE LUCA	07/10/2025	41		1	LATTE PISTACHE	3,00 €	3,00 €	SHOT SUP																					
VASCO COMPAIN	07/10/2025	41		1	OURS BLANC	2,50 €	2,50 €																						
CLARA MANHES	07/10/2025	41		1	CAPPUCCINO	1,50 €	1,50 €																						
NATHALIE GRENET	07/10/2025	41		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
EMMA CADIER	07/10/2025	41		1	CAPPUCCINO	1,50 €	1,50 €																						
LAURE SARDELLA	07/10/2025	41		1	PUMPKIN SPICE LATTE	3,50 €	3,50 €																						
NICOLAS TEROL	07/10/2025	41		1	CAPPUCCINO AVOINE	1,50 €	1,50 €																						
SUIVI CONSO GRATUITE	07/10/2025	41		11	boisson de la semaine	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/10/2025	41		23	allongé	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/10/2025	41		20	espresso	0,00 €	0,00 €																						
SUIVI CONSO GRATUITE	07/10/2025	41		6	CHOCOLAT CHAUD	0,00 €	0,00 €																						
GUILLAUME BARILLET	07/10/2025	41		1	CHICOREE	0,50 €	0,50 €																						';
    }

}
