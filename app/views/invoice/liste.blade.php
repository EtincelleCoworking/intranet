@extends('layouts.master')

@section('meta_title')
	Liste des factures
@stop

@section('content')

    <a href="{{ URL::route('invoice_add', 'F') }}" class="btn btn-primary pull-right">Ajouter une facture</a>

	<h1>Liste des factures</h1>
    @if(count($invoices)==0)
        <p>Aucun devis.</p>
    @else
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Créée le</th>
				<th>Client</th>
				<th>Echéance</th>
                <th>Paiement</th>
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
                    <a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>
                    (<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a>)
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
					<a href="{{ URL::route('invoice_modify', $invoice->id) }}" class="btn btn-sm btn-default">Modifier</a>
                    <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-sm btn-default" target="_blank">PDF</a>
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
    @endif
@stop