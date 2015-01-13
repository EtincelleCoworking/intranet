@extends('layouts.master')

@section('meta_title')
	Ajout d'une facture
@stop

@section('content')
	<h1>Nouvelle facture</h1>

	{{ Form::open(array('route' => 'invoice_add')) }}
		<p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('id' => 'selectUserId')) }}</p>
		<p>{{ Form::select('organisation_id', array(), null, array('id' => 'selectOrganisationId')) }}</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
	function getListOrganisations(id) {
		var url = "{{ URL::route('user_json_organisations') }}";
		var urlFinale = url.replace("%7Bid%7D", id);

		$('#selectOrganisationId').html('');
		$.getJSON(urlFinale, function(data) {
			var items = '';
			$.each( data, function(key, val) {
				items = items + '<option value="' + key + '">' + val + '</option>';
			});

			$('#selectOrganisationId').html(items);
		});
	}

	$('#selectUserId').on('change', function(e) {
		getListOrganisations($(this).val());
	});

	getListOrganisations($('#selectUserId').val());
});
</script>
@stop