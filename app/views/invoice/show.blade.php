@extends('layouts.master')

@section('meta_title')
    @if ($invoice->type == 'F')
        Facture
    @elseif ($invoice->type == 'D')
        Devis
    @endif
    {{ $invoice->ident }}
@stop

@section('content')
    <div class="pull-right">
        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-sm btn-primary" target="_blank">PDF</a>
    </div>
	<h1>
        @if ($invoice->type == 'F')
            Facture
        @elseif ($invoice->type == 'D')
            Devis
        @endif
        {{ $invoice->ident }}
    </h1>
    <div class="row">
        <div class="col-md-6">
            @if ($invoice->organisation)
                <p><strong>Organisme :</strong></p>
                <p>{{ $invoice->organisation->name }}</p>
            @endif
            <p><strong>Adresse de facturation :</strong></p>
            <p>{{ nl2br($invoice->address) }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Date de création :</strong></p>
            <p>{{ date('d/m/Y', strtotime($invoice->date_invoice)) }}</p>

            <p><strong>Date d'expiration :</strong></p>
            <p>{{ date('d/m/Y', strtotime($invoice->deadline)) }}</p>
            
            <p><strong>Date de paiement :</strong></p>
            <p>
                @if ($invoice->date_payment)
                    date('d/m/Y', strtotime($invoice->date_payment))
                @else
                    En attente de paiement
                @endif
            </p>
        </div>
    </div>

    <hr>
	<h2>
        Lignes 
        @if ($invoice->type == 'F')
            de la facture
        @elseif ($invoice->type == 'D')
            du devis
        @endif
    </h2>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Description</th>
                <th>Montant</th>
                <th>TVA</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $vats = array();
            $vat_total = array(
                'ht' => 0,
                'vat' => 0
            );
            ?>
            @foreach ($invoice->items as $item)
                <?php
                if (!array_key_exists($item->vat->id, $vats)) {
                    $vats[$item->vat->id] = array(
                        'base' => 0,
                        'montant' => 0,
                        'taux' => $item->vat->value
                    );
                }
                $vats[$item->vat->id]['base'] += $item->amount;
                $calc_vat = round((($item->amount * $item->vat->value) / 100), 2);
                $vats[$item->vat->id]['montant'] += $calc_vat;
                $vat_total['ht'] += $item->amount;
                $vat_total['vat'] += $calc_vat;
                ?>
            <tr>
                <td>{{ nl2br($item->text) }}</td>
                <td>{{ $item->amount }}€</td>
                <td>{{ $item->vat->value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-9">&nbsp;</div>
        <div class="col-md-3">
            <table class="table table-striped col-md-2">
                <tbody>
                    <tr>
                        <th>Total HT</th>
                        <td align="right">{{ sprintf('%0.2f', $vat_total['ht']) }}€</td>
                    </tr>
                    <tr>
                        <th>Montant TVA</th>
                        <td align="right">{{ sprintf('%0.2f', $vat_total['vat']) }}€</td>
                    </tr>
                    <tr>
                        <th>Total TTC</th>
                        <td align="right">{{ sprintf('%0.2f', ($vat_total['ht'] + $vat_total['vat'])) }}€</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop