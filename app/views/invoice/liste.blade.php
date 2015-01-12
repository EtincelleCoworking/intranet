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
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($invoices as $invoice)
			<tr>
				<td>{{ $invoice->id }}</td>
				<td>{{ $invoice->created_at }}</td>
				<td>
					<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a>
				</td>
				<td>{{ $invoice->updated_at }}</td>
				<td>
					<a href="{{ URL::route('invoice_modify', $invoice->id) }}">Modifier</a> {{ Invoice::TotalInvoice($invoice->items) }}
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