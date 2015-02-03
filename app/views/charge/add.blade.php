@extends('layouts.master')

@section('meta_title')
    Ajout d'une charge
@stop

@section('content')
    @if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif

    <h1>Nouvelle charge</h1>

    {{ Form::open(array('route' => array('charge_add'), 'files' => true)) }}
    <div class="row">
        <div class="col-md-3">
            {{ Form::label('date_charge', 'Date de la charge') }}
            <p>{{ Form::text('date_charge', date('d/m/Y'), array('class' => 'form-control datePicker')) }}</p>
            {{ Form::label('date_payment', 'Date du paiement') }}
            <p>{{ Form::text('date_payment', null, array('class' => 'form-control datePicker')) }}</p>
            {{ Form::label('deadline', 'Date d\'échéance') }}
            <p>{{ Form::text('deadline', null, array('class' => 'form-control datePicker')) }}</p>
            {{ Form::label('tags', 'Tags (séparés par ", ")') }}
            <p>{{ Form::select('tags[]', array(), null, array('class' => 'form-control tagsGet', 'multiple' => 'multiple', 'data-tags' => true)) }}</p>
            {{ Form::label('organisation_id', 'Organisation') }}
            <p>{{ Form::select('organisation_id', array(), null, array('class' => 'form-control organisationGet')) }}</p>
            {{ Form::label('document', 'Facture jointe') }}
            <p>{{ Form::file('document', null, array('class' => 'form-control')) }}</p>
        </div>
        <div class="col-md-9">
            <table class="table table-striped table-hover" id="table_rows">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>TVA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="addRow0">
                        <td>{{ Form::text('description[0]', null, array('placeholder' => 'Nouvelle ligne', 'class' => 'form-control')) }}</td>
                        <td>{{ Form::text('amount[0]', null, array('class' => 'form-control')) }}</td>
                        <td>{{ Form::select('vat_types_id[0]', VatType::SelectAll(), null, array('class' => 'form-control', 'id' =>'vat_mirror')) }}</td>
                    </tr>
                    <tr id="addRow1"></tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <a href="#" id="add_row">Ajouter une ligne</a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <p>{{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}</p>
    {{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }

    var urlJsonGetTags = "{{ URL::route('tag_json_list') }}";
    var urlJsonGetOrganisations = "{{ URL::route('organisation_json_list') }}";

    $('.datePicker').datepicker();

    $(".tagsGet").select2({
        ajax: {
        url: urlJsonGetTags,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            term: params.term
          };
        },
        processResults: function (data, page) {
          return {
            results: $.map(data, function (item) {
                    return {
                        text: item.name,
                        id: item.name
                    }
                })
          };
        },
        cache: true
      },
      minimumInputLength: 2
    });

    $(".organisationGet").select2({
        placeholder: "Cherchez une organisation",
        allowClear: true,
        ajax: {
        url: urlJsonGetOrganisations,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            term: params.term
          };
        },
        processResults: function (data, page) {
          return {
            results: $.map(data, function (item) {
                    return {
                        text: item.name,
                        id: item.id
                    }
                })
          };
        },
        cache: true
      },
      minimumInputLength: 2
    });

    var i=1;
    var vatMirror = $('#vat_mirror').html();
    $("#add_row").click(function(){
        $('#addRow'+i).html("<td><input name='description["+i+"]' type='text' placeholder='Nouvelle ligne' class='form-control'  /> </td><td><input  name='amount["+i+"]' type='text'  class='form-control'></td><td><select  name='vat_types_id["+i+"]'  class='form-control'>" + vatMirror + "</select></td>");

        $('#table_rows').append('<tr id="addRow'+(i+1)+'"></tr>');
        i++;
    });
});
</script>
@stop