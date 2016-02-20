<?php

use Illuminate\Console\Command;
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
            $excel->sheet('Etincelle Coworking', function ($sheet) {
                $sheet->freezeFirstRow();
                $sheet->setAutoSize(true);

                $sheet->appendRow(array(
                    'No Facture',
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
                                            if ($item->amount == 75) {
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
