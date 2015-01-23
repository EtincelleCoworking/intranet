@extends('layouts.master')

@section('meta_title')
	Ajout d'une facture
@stop

@section('content')
	<h1>Nouvelle facture</h1>
	{{ Form::open(array('route' => 'invoice_add')) }}
		<input type="hidden" name="last_orga" id="oldOrganisation" value="{{ $last_organisation_id }}">
        {{ Form::label('user_id', 'Client') }}
		<p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
        {{ Form::label('organisation_id', 'Organisation') }}
		<p>{{ Form::select('organisation_id', array(), null, array('id' => 'selectOrganisationId', 'class' => 'form-control')) }}</p>
        {{ Form::label('type', 'Type de la pièce') }}
		<p>
			{{ Form::select('type', array('F' => 'Facture', 'D' => 'Devis'), null, array('class' => 'form-control')) }}
		</p>
        {{ Form::label('date_invoice', 'Date de facturation') }}
		<p>{{ Form::text('date_invoice', date('Y-m-d'), array('class' => 'form-control datePicker')) }}</p>
		<p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
	{{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){
	var oldOrganisation = $('#oldOrganisation').val();
	function getListOrganisations(id) {
		var url = "{{ URL::route('user_json_organisations') }}";
		var urlFinale = url.replace("%7Bid%7D", id);

		$('#selectOrganisationId').html('');
		$.getJSON(urlFinale, function(data) {
			var items = '';
			$.each( data, function(key, val) {
				if (oldOrganisation == key) {
					items = items + '<option value="' + key + '" selected>' + val + '</option>';
				} else {
					items = items + '<option value="' + key + '">' + val + '</option>';
				}
			});

			$('#selectOrganisationId').html(items);
		});
	}

	$('#selectUserId').on('change', function(e) {
		getListOrganisations($(this).val());
	});

	getListOrganisations($('#selectUserId').val());

    $('.datePicker').datepicker({dateFormat: "yy-mm-dd"});
});
</script>
@stop