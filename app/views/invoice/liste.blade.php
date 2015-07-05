@extends('layouts.master')

@section('meta_title')
	Liste des factures
@stop

@section('content')
	@if (Auth::user()->role == 'superadmin')
    <a href="{{ URL::route('invoice_add', 'F') }}" class="btn btn-primary pull-right">Ajouter une facture</a>
    @endif

	<h1>Liste des factures</h1>

	<div class="row">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">Filtre</h4>
			</div>
			<div class="panel-body">
				{{ Form::open(array('route' => array('invoice_list'))) }}
				{{ Form::hidden('filtre_submitted', 1) }}
				@if (Auth::user()->role == 'superadmin')
					<div class="col-md-4">
						{{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_invoice.user_id') ? Session::get('filtre_invoice.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
					</div>
				@else
					{{ Form::hidden('filtre_user_id', Auth::user()->id) }}
				@endif

				<div class="col-md-2 input-group-sm">{{ Form::text('filtre_start', Session::get('filtre_invoice.start') ? date('d/m/Y', strtotime(Session::get('filtre_invoice.start'))) : date('01/12/2014'), array('class' => 'form-control datePicker')) }}</div>
				<div class="col-md-2 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_invoice.end')) ? date('d/m/Y', strtotime(Session::get('filtre_invoice.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
				<div class="col-md-2 input-group-sm">
					{{ Form::checkbox('filtre_unpaid', true, Session::has('filtre_invoice.filtre_unpaid') ? Session::get('filtre_invoice.filtre_unpaid') : false) }} Impayé

				</div>
				<div class="col-md-2">{{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-default')) }}</div>
				{{ Form::close() }}
			</div>
		</div>
	</div>

    @if(count($invoices)==0)
        <p>Aucune facture.</p>
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
			<tr
					@if($invoice->date_payment)
						class="text-muted"
								@endif
					>
				<td>{{ $invoice->ident }}</td>
				<td>{{ date('d/m/Y', strtotime($invoice->date_invoice)) }}</td>
				<td>
                    @if ($invoice->organisation)
                    	@if (Auth::user()->role == 'superadmin')
                        	<a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>
                        	(<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a> <a href="?filtre_submitted=1&filtre_user_id={{ $invoice->user->id }}"><i class="fa fa-filter"></i></a>)
                        @else
							{{ $invoice->organisation->name }}
                        @endif
                    @else
						{{ $invoice->address }}
                    @endif
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
					<a href="{{ URL::route('invoice_modify', $invoice->id) }}" class="btn btn-sm btn-default">
						@if (Auth::user()->role == 'superadmin')
							Modifier
						@else
							Consulter
						@endif
					</a>
                    <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-sm btn-default" target="_blank">PDF</a>
				</td>
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">{{ $invoices->links() }}</td>
			</tr>
		</tfoot>
	</table>
    @endif
@stop

@section('javascript')
	<script type="text/javascript">
		$().ready(function () {
			$('.datePicker').datepicker();
			$('.yearDropper').dateDropper({
				animate_current: false,
				format: "Y",
				placeholder: "{{ ((Session::get('filtre_pasttime.year'))?:date('Y')) }}"
			});
			$('.monthDropper').dateDropper({
				animate_current: false,
				format: "m",
				placeholder: "{{ ((Session::get('filtre_pasttime.month'))?:date('m')) }}"
			});
			$('#filter-client').select2();
		});
	</script>
@stop
