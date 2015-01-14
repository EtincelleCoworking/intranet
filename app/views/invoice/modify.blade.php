@extends('layouts.master')

@section('meta_title')
	Modification de la facture #{{ $invoice->id }}
@stop

@section('content')
	<h1>Modifier une facture</h1>
	<p>Organisme : {{ $invoice->organisation->name }}</p>
    <p>Client : {{ $invoice->user->fullname }}</p>

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