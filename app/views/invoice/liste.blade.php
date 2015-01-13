@extends('layouts.master')

@section('meta_title')
	Liste des factures
@stop

@section('content')
	<h1>Liste des factures</h1>
	
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Date de création</th>
				<th>Client</th>
				<th>Dernière modification</th>
				<th>Montant</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($invoices as $invoice)
			<tr>
				<td>{{ $invoice->id }}</td>
				<td>{{ $invoice->created_at->format('d/m/Y') }}</td>
				<td>
					<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a> (<a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>)
				</td>
				<td>{{ $invoice->updated_at->format('d/m/Y') }}</td>
				<td>{{ Invoice::TotalInvoice($invoice->items) }}€</td>
				<td>
					<a href="{{ URL::route('invoice_modify', $invoice->id) }}">Modifier</a>
				</td>
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">{{ $invoices->links() }}</td>
			</tr>
		</tfoot>
	</table>
@stop