@extends('layouts.master')

@section('meta_title')
	Ajout d'une facture
@stop

@section('content')
	<h1>Nouvelle facture</h1>
	{{ Form::open(array('route' => 'invoice_add')) }}
		<input type="hidden" name="last_orga" id="oldOrganisation" value="{{ $last_organisation_id }}">
		<p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('id' => 'selectUserId')) }}</p>
		<p>{{ Form::select('organisation_id', array(), null, array('id' => 'selectOrganisationId')) }}</p>
		<p>
			{{ Form::select('type', array('F' => 'Facture', 'D' => 'Devis')) }}
		</p>
        {{ Form::label('', 'Date de facturation') }}
		<p>
			{{ form_years('year', ((Input::old('year')) ? Input::old('year') : date('Y'))) }}
            {{ form_months('month', ((Input::old('month')) ? Input::old('month') : date('m'))) }}
			{{ form_days('day', ((Input::old('day')) ? Input::old('day') : date('d'))) }}
		</p>
		<p>{{ Form::submit('Ajouter') }}</p>
	{{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
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
});
</script>
@stop