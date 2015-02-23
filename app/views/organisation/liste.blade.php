@extends('layouts.master')

@section('meta_title')
	Liste des organisations
@stop

@section('content')
    <a href="{{ URL::route('organisation_add') }}" class="btn btn-primary pull-right">Ajouter une organisation</a>

    <h1>Liste des organisations</h1>

	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom</th>
                <th>Code achat</th>
                <th>Code vente</th>
				<th>Dernière modification</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($organisations as $orga)
			<tr>
				<td>{{ $orga->id }}</td>
				<td>
					<a href="{{ URL::route('organisation_modify', $orga->id) }}">{{ $orga->name }}</a>
				</td>
                <td>{{ $orga->code_purchase }}</td>
                <td>{{ $orga->code_sale }}</td>
				<td>{{ $orga->updated_at }}</td>
				<td>
					<a href="{{ URL::route('invoice_add_organisation', array('D', $orga->id)) }}" class="btn btn-xs btn-success">Devis</a>
					<a href="{{ URL::route('invoice_add_organisation', array('F', $orga->id)) }}" class="btn btn-xs btn-primary">Facture</a>
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
@stop