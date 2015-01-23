@extends('layouts.master')

@section('meta_title')
	Modification
    @if ($invoice->type == 'F')
        de la facture
    @elseif ($invoice->type == 'D')
        du devis
    @endif
    {{ $invoice->ident }}
@stop

@section('content')
	<h1>
        Modifier
        @if ($invoice->type == 'F')
            la facture
        @elseif ($invoice->type == 'D')
            le devis
        @endif
        {{ $invoice->ident }}
    </h1>

    {{ Form::model($invoice, array('route' => array('invoice_modify', $invoice->id))) }}
        <div class="row">
            <div class="col-md-6">
                <p>Organisme : {{ $invoice->organisation->name }}</p>
                <p>Client : {{ $invoice->user->fullname }}</p>
            </div>
            <div class="col-md-6">
                {{ Form::label('date_invoice', 'Date de création') }}
                <p>{{ Form::text('date_invoice', null, array('class' => 'form-control datePicker')) }}</p>

                {{ Form::label('deadline', 'Date d\'expiration') }}
                <p>{{ Form::text('deadline', null, array('class' => 'form-control datePicker')) }}</p>
                <br>
                <p>
                    {{ Form::label('isPaidCheck', 'Cochez pour entrer la date de paiement') }} {{ Form::checkbox('is_paid', true, (($invoice->date_payment) ? true : false), array('id' => 'isPaidCheck')) }}
                </p>

                <div id="showPaymentDate">
                    {{ Form::label('date_payment', 'Date de paiement') }}
                    <p>{{ Form::text('date_payment', null, array('class' => 'form-control datePicker')) }}</p>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12" align="center">
                {{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}
            </div>
        </div>
    {{ Form::close() }}

    <hr>
	<h2>Lignes de la facture</h2>
 	{{ Form::model($invoice->items, array('route' => array('invoice_item_modify', $invoice->id), 'autocomplete' => 'off')) }}
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Ressource</th>
                <th>Description</th>
                <th>Montant</th>
                <th>TVA</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
            <tr>
                <td>{{ Form::select('ressource_id['.$item->id.']', Ressource::SelectAll(), $item->ressource_id, array('class' => 'form-control')) }}</td>
                <td>{{ Form::textarea('text['.$item->id.']', $item->text, array('rows' => 4, 'class' => 'form-control')) }}</td>
                <td>{{ Form::text('amount['.$item->id.']', $item->amount, array('class' => 'form-control')) }}</td>
                <td>{{ Form::select('vat_types_id['.$item->id.']', VatType::SelectAll(), $item->vat->id, array('class' => 'form-control')) }}</td>
                <td><a href="{{ URL::route('invoice_item_delete', array($invoice->id, $item->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer cette ligne ?" rel="nofollow">Retirer</a</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>{{ Form::select('ressource_id[0]', Ressource::SelectAll(), null, array('class' => 'form-control')) }}</td>
                <td>{{ Form::textarea('text[0]', null, array('rows' => 4, 'placeholder' => 'Nouvelle ligne', 'class' => 'form-control')) }}</td>
                <td>{{ Form::text('amount[0]', null, array('class' => 'form-control')) }}</td>
                <td>{{ Form::select('vat_types_id[0]', VatType::SelectAll(), null, array('class' => 'form-control')) }}</td>
            </tr>
        </tfoot>
    </table>
	{{ Form::submit('Modifier les lignes', array('class' => 'btn btn-info')) }}
	{{ Form::close() }}

    @if ($invoice->type == 'D')
    <hr>
    <div class="row">
        <div class="col-md-6" align="center">
            <a href="{{ URL::route('invoice_validate', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir passer ce devis en facture ?" rel="nofollow" class="btn btn-success">Passer en facture</a>
        </div>
        <div class="col-md-6" align="center">
            <a href="{{ URL::route('invoice_delete', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir supprimer ce devis ?" rel="nofollow" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
    @endif
@stop

@section('javascript')
<script type="text/javascript">
    $().ready(function() {
        function activPaid(e) {
            if (e.is(':checked')) {
                $('#showPaymentDate').show('slow');
            } else {
                $('#showPaymentDate').hide('slow');
            }
        }

        activPaid($('#isPaidCheck'));
        $('#isPaidCheck').on('change', function() {
            activPaid($(this));
        });

        $('.datePicker').datepicker({dateFormat: "yy-mm-dd"});
    });
</script>
@stop