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
        $export_infos = Excel::create($this->argument('filename') . '-' . date('Y-m-d'), function ($excel) {
            $excel->sheet('Ventes', function ($sheet) {
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
                            $row[] = sprintf('%06d', $invoice->organisation_id);
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

                            $sheet->appendRow($row);
                        }
                    }
                }


            });
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

                Stripe::setApiKey("sk_live_qGpkjeWcrIHjCafX0VYzVqca");
                do {
                    $params = array('limit' => 100);
                    $params['created']['gt'] = mktime(0, 0, 0, 1, 1, 2015);
                    if (isset($item)) {
                        $params['starting_after'] = $item->id;
                    }
                    $Transfers = Transfer::all($params);
                    foreach ($Transfers->data as $Transfer) {
                        $items = BalanceTransaction::all(array('limit' => 100, 'transfer' => $Transfer->id, 'type' => 'charge'));
                        //print_r($items);exit;
                        foreach ($items->data as $item) {
                            $this->output->write('.');
                            //print_r($item);exit;
                            $row = array();
                            $row[] = $Transfer->id;
                            $row[] = date('d/m/Y', $item->available_on);
                            $row[] = $item->description;
                            $row[] = $item->amount / 100;
                            //print_r($reversal);
                            $row[] = $item->fee / 100;
                            $row[] = $item->net / 100;
                            $sheet->appendRow($row);
                        }
                        $sheet->appendRow(array('', '', '', '', '', '', $Transfer->amount / 100));
                        $this->output->writeln('');
                    }
                } while ($Transfers->has_more);

            });
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
            array('from', null, InputOption::VALUE_OPTIONAL, 'Date de début de l\'export comptable.', null),
            array('to', null, InputOption::VALUE_OPTIONAL, 'Date de fin de l\'export comptable.', null),
        );
    }

}
