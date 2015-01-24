@extends('layouts.master')

@section('meta_title')
	Liste des pays
@stop

@section('content')
    <a href="{{ URL::route('country_add') }}" class="btn btn-primary pull-right">Ajouter un pays</a>
    <h1>Liste des pays</h1>

	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($countries as $country)
			<tr>
				<td>{{ $country->id }}</td>
				<td>
					<a href="{{ URL::route('country_modify', $country->id) }}">{{ $country->name }}</a>
				</td>
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">{{ $countries->links() }}</td>
			</tr>
		</tfoot>
	</table>
@stop