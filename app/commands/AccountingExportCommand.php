<?php

use Illuminate\Console\Command;
use Stripe\BalanceTransaction;
use Stripe\Stripe;
use Stripe\Transfer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AccountingExportCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'accounting:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporte les écritures comptables sur une période';

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
        $export_infos = Excel::create($this->argument('filename') . '-sales-' . date('Y-m-d'), function ($excel) {
            $excel->sheet('Ventes', function ($sheet) {

//                $sheet->appendRow(array(
//                    'Client',
//                    'Date Facture',
//                    'Code Comptable',
//                    'Client',
//                    'Facture',
//                    'Description',
//                    '',
//                    '',
//                ));
//
                foreach (Invoice::InvoiceOnly()->orderBy('days', 'asc')->orderBy('number', 'asc')->get() as $invoice) {

                    foreach ($invoice->items as $item) {
                        if ($item->amount <> 0) {
                            //region libellé
                            $row2 = array();
                            if ($invoice->organisation) {
                                $row2[] = trim($invoice->organisation->name);
                            } else {
                                $row2[] = trim(preg_replace('/\n.*/', '', $invoice->address));
                            }
//                            $row2[] = 'Wilson';
                            try {
                                $row2[] = $item->ressource->kind->name;
                            } catch (\Exception $e) {
                                try {
                                    $row2[] = $item->ressource->name;
                                } catch (\Exception $e) {
                                    $row2[] = '';
                                }
                            }
//                            $row2[] = trim(preg_replace('/\n/', ' ', $item->text));
                            $row2 = preg_replace('/\n/', '', implode(' // ', $row2));
                            //endregion

                            //region Line 1: Client
                            $row = array();
                            $row[] = 'VTE';
                            $row[] = date('d/m/Y', strtotime($invoice->date_invoice));
                            $row[] = '411000';
                            $row[] = sprintf('9%05d', $invoice->organisation_id);
                            $row[] = str_replace('-', '', $invoice->ident);
                            $row[] = $row2;
                            $row[] = sprintf('%0.2f', $item->amount * (1 + ($item->vat->value) / 100));
                            $row[] = '';
                            $sheet->appendRow($row);
                            //endregion
                            //region Line 2: TVA
                            if ($item->vat->value) {
                                $row = array();
                                $row[] = 'VTE';
                                $row[] = date('d/m/Y', strtotime($invoice->date_invoice));
                                $row[] = '445710';
                                $row[] = '';
                                $row[] = $invoice->ident;
                                $row[] = $row2;
                                $row[] = '';
                                $row[] = sprintf('%0.2f', $item->amount * ($item->vat->value) / 100);
                                $sheet->appendRow($row);
                            }
                            //endregion
                            //region Line 3: Produit
                            $row = array();
                            $row[] = 'VTE';
                            $row[] = date('d/m/Y', strtotime($invoice->date_invoice));
                            $row[] = $this->getAccountingCode($item);
                            $row[] = '';
                            $row[] = $invoice->ident;
                            $row[] = $row2;
                            $row[] = '';
                            $row[] = sprintf('%0.2f', $item->amount);
                            $sheet->appendRow($row);
                            //endregion
                        }
                    }
                }


            });

        })->store('csv', false, true);
        $this->output->writeln(sprintf('Le fichier %s a été créé', $export_infos['full']));

        $export_infos = Excel::create($this->argument('filename') . '-' . date('Y-m-d'), function ($excel) {
            $excel->sheet('Ventes', function ($sheet) {

                $styleArray = [
//                    'font' => [
//                        'bold' => true,
//                    ],
//                    'alignment' => [
//                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
//                    ],
//                    'borders' => [
//                        'top' => [
//                            'borderStyle' => PHPExcel_Style_Border::BORDER_THIN,
//                        ],
//                    ],
                    'fill' => [
                        'fillType' => PHPExcel_Style_Fill::FILL_SOLID,
//                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'FFFF0000',
                        ],
//                        'endColor' => [
//                            'argb' => 'FFFFFFFF',
//                        ],
                    ],
                ];

                $sheet->freezeFirstRow();
                $sheet->setAutoSize(true);

                $sheet->appendRow(array(
                    'No Facture',
                    'Date Facture',
                    'No Client',
                    'Nom Client',
                    'Site',
                    'Catégorie',
                    'Produit',
                    'Description',
                    'HT',
                    'TVA',
                    'TTC',
                    'Paiement',
                ));

                foreach (Invoice::InvoiceOnly()->orderBy('days', 'asc')->orderBy('number', 'asc')->get() as $invoice) {

                    foreach ($invoice->items as $item) {
                        if ($item->amount <> 0) {
                            $row = array();

                            $row[] = $invoice->ident;
                            $row[] = date('d/m/Y', strtotime($invoice->date_invoice));
                            $row[] = sprintf('9%05d', $invoice->organisation_id);
                            if ($name = preg_replace('/\n.+/', '', $invoice->address)) {
                                $row[] = $name;
                            } else {
                                $row[] = $invoice->organisation->name;
                            }
                            $row[] = 'Wilson';
                            try {
                                $row[] = $item->ressource->kind->name;
                            } catch (\Exception $e) {
                                try {
                                    $row[] = $item->ressource->name;
                                } catch (\Exception $e) {
                                    $row[] = '';
                                }
                            }
                            try {
                                if ($item->ressource_id == Ressource::TYPE_COWORKING) {
                                    switch ($item->subscription_hours_quota) {
                                        case -1:
                                            if (in_array($item->amount, array(220, 165, 200, 250))) {
                                                $row[] = 'Coworking - Illimité';
                                            } elseif ($item->amount == 300) {
                                                $row[] = 'Coworking - Poste fixe';
                                            } else {
                                                $row[] = sprintf('Coworking - illimité (%0.2f€)', $item->amount);
                                            }
                                            break;
                                        case 40:
                                            $row[] = 'Coworking - Forfait 40h';
                                            break;
                                        case 80:
                                            $row[] = 'Coworking - Forfait 80h';
                                            break;
                                        default:
                                            if (in_array($item->amount, array(220, 165, 200, 250))) {
                                                $row[] = 'Coworking - Illimité';
                                            } elseif ($item->amount == 300) {
                                                $row[] = 'Coworking - Poste fixe';
                                            } elseif ($item->amount == 75) {
                                                $row[] = 'Coworking - 10 demi journées';
                                            } else {
                                                $row[] = 'Coworking - Détail';
                                            }
                                    }
                                } else {
                                    $row[] = $item->ressource->name;
                                }
                            } catch
                            (\Exception $e) {
                                $row[] = '';
                            }
                            $row[] = $item->text;
                            $row[] = sprintf('%0.2f', $item->amount);
                            $row[] = sprintf('%0.2f', $item->amount * $item->vat->value / 100);
                            $row[] = sprintf('%0.2f', $item->amount * (1 + ($item->vat->value) / 100));
                            $row[] = $invoice->date_payment ? date('d/m/Y', strtotime($invoice->date_payment)) : '';

                            $sheetRow = $sheet->appendRow($row);
                            if($invoice->is_lost){
                                $sheet->getStyle(sprintf('A%1$d:%2$s%1$d', $sheetRow->getRowIndex(), chr(ord('A' + count($row)))))->applyFromArray($styleArray);
                            }
                        }
                    }
                }


            });
            /*
            $excel->sheet('Stripe', function ($sheet) {
                $sheet->freezeFirstRow();
                $sheet->setAutoSize(true);

                $sheet->appendRow(array(
                    'Transaction',
                    'Date',
                    'Facture',
                    'Montant',
                    'Commission',
                    'Solde',
                    'Total virement',
                ));

                Stripe::setApiKey($_ENV['stripe_sk']);
                do {
                    $params = array('limit' => 100);
                    $params['created']['gt'] = mktime(0, 0, 0, 1, 1, 2015);
                    if (isset($Transfer)) {
                        $params['starting_after'] = $Transfer->id;
                    }
                    $Transfers = Transfer::all($params);
                    foreach ($Transfers->data as $Transfer) {
                        try {

                            $items = BalanceTransaction::all(array('limit' => 100, 'transfer' => $Transfer->id, 'type' => 'charge'));
                            foreach ($items->data as $item) {
                                $this->output->write('.');
                                $row = array();
                                $row[] = $Transfer->id;
                                $row[] = date('d/m/Y', $item->available_on);
                                $row[] = $item->description;
                                $row[] = $item->amount / 100;
                                $row[] = $item->fee / 100;
                                $row[] = $item->net / 100;
                                $sheet->appendRow($row);
                            }
                            $sheet->appendRow(array('', '', '', '', '', '', $Transfer->amount / 100));
                            $this->output->writeln('');
                        } catch (\Stripe\Error\InvalidRequest $e) {
                            $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                        }
                    }
                } while ($Transfers->has_more);

            });
            */
        })->store('xls', false, true);
        $this->output->writeln(sprintf('Le fichier %s a été créé', $export_infos['full']));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected
    function getArguments()
    {
        return array(
            array('filename', InputArgument::REQUIRED, 'Nom du fichier vers lequel exporter les données en .xls'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected
    function getOptions()
    {
        return array(
//            array('from', null, InputOption::VALUE_OPTIONAL, 'Date de début de l\'export comptable.', null),
//            array('to', null, InputOption::VALUE_OPTIONAL, 'Date de fin de l\'export comptable.', null),
        );
    }

    private function getAccountingCode($item)
    {
        if ($item->ressource_id == Ressource::TYPE_COWORKING) {
            switch ($item->subscription_hours_quota) {
                case -1:
                    if (in_array($item->amount, array(220, 165, 200, 250))) {
                        return '706150';
                        //$row[] = 'Coworking - Illimité';
                    } elseif ($item->amount == 300) {
                        return '706160';
                        //$row[] = 'Coworking - Poste fixe';
                    } else {
                        return '706150';
                        //$row[] = sprintf('Coworking - illimité (%0.2f€)', $item->amount);
                    }
                    break;
                case 40:
                    return '706130';
                    //$row[] = 'Coworking - Forfait 40h';
                    break;
                case 80:
                    return '706140';
                    //$row[] = 'Coworking - Forfait 80h';
                    break;
                default:
                    if (in_array($item->amount, array(220, 165, 200, 250))) {
                        return '706150';
                        //$row[] = 'Coworking - Illimité';
                    } elseif ($item->amount == 300) {
                        return '706160';
                        //$row[] = 'Coworking - Poste fixe';
                    } elseif ($item->amount == 75) {
                        return '706110';
                        //$row[] = 'Coworking - 10 demi journées';
                    } else {
                        return '706120';
                        //$row[] = 'Coworking - Détail';
                    }
            }
        } else {
            switch ($item->ressource_id) {
                case 4: // Salle conférence
                    return '708130';
                // 708230 Carmes
                // 708330 Montauban
                case 3: // Salle de réunion 10-12
                    return '708140';
                // 708240 Carmes
                // 708340 Montauban
                case 8: // Salon
                    return '708160';
                // 708260 Carmes
                // 708360 Montauban
                case 2: // Salle de réunion 4-6
                    return '708150';
                // 708250 Carmes
                // 708350 Montauban
                case 6: // Traiteur
                    return '707110';
                // 707120 Carmes
                // 707130 Montauban

                case 9: // Domiciliation
                    return '708110';
                // 708210 Carmes
                // 708310 Montauban
                case 7: // Formation
                    return '708120';
                // 708220 Carmes
                // 708320 Montauban
                case 12: // Ulule & co
                    return '708810';
                case 11: // Salle de réunion 6 personnes (Carmes)
                    return '708250';
                case 13: // Salle de réunion 6 personnes (Montauban)
                    return '708350';
                case 14: // Salle de conférence (Carmes)
                    return '708230';
            }

            if ($item->ressource) {
                return $item->ressource->name;
            }
            return $item->id;
        }
    }

}
