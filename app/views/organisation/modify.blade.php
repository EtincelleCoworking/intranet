@extends('layouts.master')

@section('meta_title')
    Modification de l'organisation #{{ $organisation->id }}
@stop

@section('content')
    <h1>Modifier une organisation</h1>

    <div class="row">
        <div class="col-md-6">
            {{ Form::model($organisation, array('route' => array('organisation_modify', $organisation->id))) }}
            <div class="row">
                <div class="col-md-12">
                    {{ Form::label('name', 'Nom') }}
                    <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {{ Form::label('address', 'Adresse') }}
                    <p>{{ Form::textarea('address', null, array('class' => 'form-control', 'rows' => 3)) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('zipcode', 'Code postal') }}
                    <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
                </div>
                <div class="col-md-6">
                    {{ Form::label('city', 'Ville') }}
                    <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {{ Form::label('country_id', 'Pays') }}
                    <p>{{ Form::select('country_id', Country::Select(), null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {{ Form::label('tva_number', 'TVA') }}
                    <p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    {{ Form::label('code_purchase', 'Code achat') }}
                    <p>{{ Form::text('code_purchase', null, array('class' => 'form-control')) }}</p>
                </div>
                <div class="col-md-6">
                    {{ Form::label('code_sale', 'Code vente') }}
                    <p>{{ Form::text('code_sale', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
            {{ Form::close() }}
        </div>
        <div class="col-md-6">
            {{ Form::model($organisation, array('route' => array('organisation_add_user', $organisation->id))) }}
            <table class="table table-striped table-hover">
                <caption>Liste des membres</caption>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($organisation->users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td><a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a></td>
                        <td><a href="{{ URL::route('organisation_delete_user', array($organisation->id, $user->id)) }}"
                               data-method="delete"
                               data-confirm="Etes-vous certain de vouloir retirer {{ $user->fullname }} ?"
                               rel="nofollow">Retirer</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td></td>
                    <td>{{ Form::select('user_id', User::SelectNotInOrganisation($organisation->id, 'Sélectionnez un utilisateur'), null, array('class' => 'form-control', 'id' => 'user_selector')) }}</td>
                    <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-info')) }}</td>
                </tr>
                </tfoot>
            </table>
            {{ Form::close() }}
        </div>
    </div>

    <div class="row">
        <a href="{{ URL::route('invoice_add_organisation', array('type' => 'F', 'organisation' =>$organisation->id)) }}"
           class="btn btn-primary pull-right">Ajouter une facture</a>

        <h2>Liste des devis/factures</h2>

        <table class="table table-hover">
            <thead>
            <tr>
                <th>#</th>
                <th>Créée le</th>
                <th>Type</th>
                <th>Echéance</th>
                <th>Paiement</th>
                <th>Montant</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($organisation->invoices as $invoice)
                <tr>
                    <td>{{ $invoice->ident }}</td>
                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if ($invoice->type == 'D')
                            Devis
                        @else
                            Facture
                        @endif
                    </td>
                    <td>
                        @if ($invoice->date_canceled)
                            <span class="badge badge-danger">Refusé</span>
                        @else
                            @if (!$invoice->date_payment)
                                @if ($invoice->daysDeadline > 7)
                                    <span class="badge badge-success">
	                        @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                            <span class="badge badge-warning">
	                        @else
                                                    <span class="badge badge-danger">
	                        @endif

                                                        {{ date('d/m/Y', strtotime($invoice->deadline)) }}
	                        </span>
                                @else
                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                @endif
                            @endif
                    </td>
                    <td>{{ (($invoice->date_payment) ? date('d/m/Y', strtotime($invoice->date_payment)) : '') }}</td>
                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
                    <td>
                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}" class="btn btn-sm btn-default">Modifier</a>
                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-sm btn-default"
                           target="_blank">PDF</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

            $('#user_selector').select2();
        });
    </script>
@stop
