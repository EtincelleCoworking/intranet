@extends('layouts.master')

@section('meta_title')
	Liste des sociétés
@stop

@section('breadcrumb')
	<div class="row wrapper border-bottom white-bg page-heading">
		<div class="col-sm-4">
			<h2>Liste des sociétés</h2>
		</div>
		<div class="col-sm-8">
			@if (Auth::user()->isSuperAdmin())
				<div class="title-action">
					<a href="{{ URL::route('organisation_add') }}" class="btn btn-default">Ajouter une société</a>
				</div>
			@endif
		</div>
	</div>
@stop



@section('content')
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox ">
				<div class="ibox-content">
					<div class="row">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th>Nom</th>
								<th>Code achat</th>
								<th>Code vente</th>
								<th>Actions</th>
							</tr>
							</thead>
							<tbody>
							@foreach ($organisations as $organisation)
								<tr>
									<td>
										<a href="{{ URL::route('organisation_modify', $organisation->id) }}">{{ $organisation->name }}</a>
									</td>
									<td>{{ $organisation->code_purchase }}</td>
									<td>{{ $organisation->code_sale }}</td>
									<td>
										<a href="{{ URL::route('organisation_modify', $organisation->id) }}"" class="btn btn-xs btn-default">Modifier</a>
										<a href="{{ URL::route('invoice_add_organisation', array('D', $organisation->id)) }}" class="btn btn-xs btn-default btn-outline">Ajouter un devis</a>
										<a href="{{ URL::route('invoice_add_organisation', array('F', $organisation->id)) }}" class="btn btn-xs btn-default btn-outline">Ajouter une facture</a>
									</td>
								</tr>
							@endforeach
							</tbody>
							<tfoot>
							<tr>
								<td colspan="6">{{ $organisations->links() }}</td>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@stop
@section('content')

@stop