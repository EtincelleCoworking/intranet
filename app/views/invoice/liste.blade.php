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
				<th>Date d'échéance</th>
                <th>Date de paiement</th>
				<th>Montant</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($invoices as $invoice)
			<tr>
				<td>{{ $invoice->ident }}</td>
				<td>{{ $invoice->created_at->format('d/m/Y') }}</td>
				<td>
					<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a> (<a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>)
				</td>
                <td>
                    @if (!$invoice->date_payment)
                        @if ($invoice->daysDeadline > 7)
                        <span class="badge badge-success">
                        @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                        <span class="badge badge-warning">
                        @else
                        <span class="badge badge-danger">
                        @endif

                        {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                        </span>
                    @else
                        {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                    @endif
                </td>
				<td>{{ (($invoice->date_payment) ? date('d/m/Y', strtotime($invoice->date_payment)) : '') }}</td>
				<td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
				<td>
					<a href="{{ URL::route('invoice_modify', $invoice->id) }}">Modifier</a>
                    <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}">PDF</a>
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