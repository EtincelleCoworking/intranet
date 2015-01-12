@extends('layouts.master')

@section('meta_title')
	Liste des utilisateurs
@stop

@section('content')
	<h1>Liste des utilisateurs</h1>
	
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom complet</th>
				<th>Adresse email</th>
				<th>Dernière modification</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($users as $user)
			<tr>
				<td>{{ $user->id }}</td>
				<td>{{ $user->fullname }}</td>
				<td>{{ $user->email }}</td>
				<td>{{ $user->updated_at }}</td>
				<td></td>
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">{{ $users->links() }}</td>
			</tr>
		</tfoot>
	</table>
@stop