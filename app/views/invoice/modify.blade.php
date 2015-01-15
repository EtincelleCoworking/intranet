@extends('layouts.master')

@section('meta_title')
	Modification
    @if ($invoice->type == 'F')
        de la facture
    @elseif ($invoice->type == 'D')
        du devis
    @endif
    {{ $invoice->ident }}
@stop

@section('content')
	<h1>
        Modifier
        @if ($invoice->type == 'F')
            la facture
        @elseif ($invoice->type == 'D')
            le devis
        @endif
        {{ $invoice->ident }}
    </h1>
	<p>Organisme : {{ $invoice->organisation->name }}</p>
    <p>Client : {{ $invoice->user->fullname }}</p>

    {{ Form::model($invoice, array('route' => array('invoice_modify', $invoice->id))) }}
        {{ Form::hidden('date_invoice', $invoice->date_invoice) }}
        {{ Form::label('', 'Date de facturation') }}
        <p>
            {{ form_years('year', $date_explode[0]) }}
            {{ form_months('month', $date_explode[1]) }}
            {{ form_days('day', $date_explode[2]) }}
        </p>
        <p>{{ Form::submit('Modifier') }}</p>
    {{ Form::close() }}

	<h2>Lignes de la facture</h2>
 	{{ Form::model($invoice->items, array('route' => array('invoice_item_modify', $invoice->id), 'autocomplete' => 'off')) }}
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Ressource</th>
                <th>Description</th>
                <th>Montant</th>
                <th>TVA</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
            <tr>
                <td>{{ Form::select('ressource_id['.$item->id.']', Ressource::SelectAll(), $item->ressource_id) }}</td>
                <td>{{ Form::textarea('text['.$item->id.']', $item->text, array('rows' => 4)) }}</td>
                <td>{{ Form::text('amount['.$item->id.']', $item->amount) }}</td>
                <td>{{ Form::select('vat_types_id['.$item->id.']', VatType::SelectAll(), $item->vat->id) }}</td>
                <td><a href="{{ URL::route('invoice_item_delete', array($invoice->id, $item->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer cette ligne ?" rel="nofollow">Retirer</a</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>{{ Form::select('ressource_id[0]', Ressource::SelectAll()) }}</td>
                <td>{{ Form::textarea('text[0]', null, array('rows' => 4, 'placeholder' => 'Nouvelle ligne')) }}</td>
                <td>{{ Form::text('amount[0]') }}</td>
                <td>{{ Form::select('vat_types_id[0]', VatType::SelectAll()) }}</td>
            </tr>
        </tfoot>
    </table>
	{{ Form::submit('Modifier') }}
	{{ Form::close() }}

    @if ($invoice->type == 'D')
    <a href="{{ URL::route('invoice_validate', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir passer ce devis en facture ?" rel="nofollow">Valider</a>
    @endif
@stop