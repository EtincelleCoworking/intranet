@extends('layouts.master')

@section('meta_title')
    Nouvelle dépense
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Nouvelle dépense</h2>
        </div>
        <div class="col-sm-8">

        </div>
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">
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
                            {{ Form::label('organisation_id', 'Société') }}
                            <p>{{ Form::select('organisation_id', Organisation::SelectAll('Sélectionnez une société'), null, array('class' => 'form-control organisationGet')) }}</p>
                            {{ Form::label('document', 'Justificatif') }}
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
                                        <a href="#" id="add_row" class="btn btn-default">Ajouter une ligne</a>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>


                <div class="hr-line-dashed"></div>
                <div class="form-group">
                {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                <a href="{{ URL::route('charge_list', 'all') }}" class="btn btn-white">Annuler</a>
                    </div>
                {{ Form::close() }}
                </div>
            </div>

        </div>
    </div>


@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){

    $('.datePicker').datepicker();
    $('.organisationGet').select2();


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