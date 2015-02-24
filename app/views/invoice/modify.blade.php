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
    <div class="pull-right">
        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-sm btn-primary" target="_blank">PDF</a>
    </div>
    
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
                @if ($invoice->organisation)
                    <p>Organisme : {{ $invoice->organisation->name }}</p>
                    <p>Client : {{ $invoice->user->fullname }}</p>
                @endif
                {{ Form::label('address', 'Adresse de facturation') }}
                <p>{{ Form::textarea('address', $invoice->address, array('class' => 'form-control', 'rows' => '5')) }}</p>
            </div>
            <div class="col-md-6">
                {{ Form::label('date_invoice', 'Date de création') }}
                <p>{{ Form::text('date_invoice', date('d/m/Y', strtotime($invoice->date_invoice)), array('class' => 'form-control datePicker')) }}</p>

                {{ Form::label('deadline', 'Date d\'expiration') }}
                <p>{{ Form::text('deadline', date('d/m/Y', strtotime($invoice->deadline)), array('class' => 'form-control datePicker')) }}</p>
                <br>
                <p>
                    {{ Form::label('isPaidCheck', 'Cochez pour entrer la date de paiement') }} {{ Form::checkbox('is_paid', true, (($invoice->date_payment) ? true : false), array('id' => 'isPaidCheck')) }}
                </p>

                <div id="showPaymentDate">
                    {{ Form::label('date_payment', 'Date de paiement') }}
                    <p>{{ Form::text('date_payment', (($invoice->date_payment) ? date('d/m/Y', strtotime($invoice->date_payment)) : null), array('class' => 'form-control datePicker')) }}</p>
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
        <div class="col-md-4" align="center">
            <a href="{{ URL::route('invoice_validate', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir passer ce devis en facture ?" rel="nofollow" class="btn btn-success">Passer en facture</a>
        </div>
        <div class="col-md-4" align="center">
            <a href="{{ URL::route('invoice_cancel', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir passer ce devis en refusé ?" rel="nofollow" class="btn btn-warning">Devis refusé</a>
        </div>
        <div class="col-md-4" align="center">
            <a href="{{ URL::route('invoice_delete', $invoice->id) }}" data-method="get" data-confirm="Etes-vous certain de vouloir supprimer ce devis ?" rel="nofollow" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
    @endif
    
    <br />
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Commentaires</h3>
        </div>
        <div class="panel-body">
            @foreach ($invoice->comments as $comment)
            <div class="media">
                <div class="media-body">
                    <h4 class="media-heading">Par {{ $comment->user->fullname }}</h4>
                    <p><i>Le {{ date('d/m/Y \à H:i', strtotime($comment->created_at)) }}</i></p>
                    <p>{{ nl2br($comment->content) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <br />
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">Nouveau commentaire</h3>
        </div>
        <div class="panel-body">
            {{ Form::open(array('route' => array('invoice_comment_add', $invoice->id))) }}
                {{ Form::hidden('invoice_id', $invoice->id) }}
                {{ Form::hidden('user_id', Auth::user()->id) }}
                <p>{{ Form::textarea('content', null, array('class' => 'form-control')) }}</p>
                {{ Form::submit('Ajouter', array('class' => 'btn btn-success')) }}
            {{ Form::close() }}
        </div>
    </div>
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

        $('.datePicker').datepicker();
    });
</script>
@stop