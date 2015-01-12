@extends('layouts.master')

@section('meta_title')
	Modification de la facture #{{ $invoice->id }}
@stop

@section('content')
	<h1>Modifier une facture</h1>

	<h2>Lignes de la facture</h2>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Description</th>
				<th>Montant</th>
				<th>TVA</th>
			</tr>
		</thead>
		<tbody>
		
			@foreach ($invoice->invoices_items as $item)
			<tr>
				<td>{{ $item->text }}</td>
				<td>{{ $item->amount }}</td>
				<td>{{ $item->vat_types->value }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
@stop