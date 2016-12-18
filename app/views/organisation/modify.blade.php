@extends('layouts.master')

@section('meta_title')
    Modification de la société {{$organisation->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Modification de la société {{$organisation->name}}</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Société</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">

                        {{ Form::model($organisation, array('route' => array('organisation_modify', $organisation->id))) }}
                        {{ Form::label('name', 'Nom') }}
                        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        {{ Form::label('address', 'Adresse') }}
                        <p>{{ Form::textarea('address', null, array('class' => 'form-control', 'rows' => 3)) }}</p>

                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('zipcode', 'Code postal') }}
                                <p>{{ Form::text('zipcode', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('city', 'Ville') }}
                                <p>{{ Form::text('city', null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('country_id', 'Pays') }}
                                <p>{{ Form::select('country_id', Country::Select(), 73, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('accountant_id', 'Contact facturation') }}
                                <p>{{ Form::select('accountant_id', User::SelectInOrganisation($organisation->id, ''), null, array('class' => 'form-control')) }}</p>
                                {{--{{ Form::label('code_purchase', 'Code achat') }}--}}
                                {{--<p>{{ Form::text('code_purchase', null, array('class' => 'form-control')) }}</p>--}}
                            </div>
                            <div class="col-md-4">
                                {{--{{ Form::label('code_sale', 'Code vente') }}--}}
                                {{--<p>{{ Form::text('code_sale', null, array('class' => 'form-control')) }}</p>--}}
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('tva_number', 'TVA') }}
                                <p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::label('domiciliation_kind_id', 'Domiciliation') }}
                                <p>{{ Form::select('domiciliation_kind_id', DomiciliationKind::select(), null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('domiciliation_start_at', 'Début') }}
                                <p>{{ Form::text('domiciliation_start_at', $organisation->domiciliation_start_at?date('d/m/Y', strtotime($organisation->domiciliation_start_at)):null, array('class' => 'form-control datePicker')) }}</p>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('domiciliation_end_at', 'Fin') }}
                                <p>{{ Form::text('domiciliation_end_at', $organisation->domiciliation_end_at?date('d/m/Y', strtotime($organisation->domiciliation_end_at)):null, array('class' => 'form-control datePicker')) }}</p>
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('organisation_list') }}" class="btn btn-white">Annuler</a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Membres</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::model($organisation, array('route' => array('organisation_add_user', $organisation->id))) }}
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($organisation->users as $user)
                            <tr>
                                <td><a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a></td>
                                <td>
                                    <a href="{{ URL::route('organisation_delete_user', array($organisation->id, $user->id)) }}"
                                       data-confirm="Etes-vous certain de vouloir retirer {{ $user->fullname }} ?"
                                       class="btn btn-danger btn-xs btn-outline"
                                       rel="nofollow">Supprimer</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>{{ Form::select('user_id', User::SelectNotInOrganisation($organisation->id, 'Sélectionnez un utilisateur'), null, array('class' => 'form-control', 'id' => 'user_selector')) }}</td>
                            <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-default')) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Factures</h5>

                    <div class="pull-right">
                        <a href="{{ URL::route('organisation_remind', array('id' => $organisation->id)) }}"
                           class="btn btn-xs btn-default">Relancer</a>
                        <a href="{{ URL::route('invoice_add_organisation', array('type' => 'F', 'organisation' =>$organisation->id)) }}"
                           class="btn btn-xs btn-default">Ajouter une facture</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Créée le</th>
                            <th>Echéance</th>
                            <th>Montant HT</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($organisation->invoices as $invoice)
                            @if($invoice->type == 'F')
                                <tr
                                        @if($invoice->date_payment)
                                        class="text-muted"
                                        @elseif($invoice->is_lost)
                                        class="text-danger"
                                        @endif
                                >
                                    <td>{{ $invoice->ident }}</td>
                                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($invoice->date_canceled)
                                            <span class="badge badge-danger">Refusé</span>
                                        @else
                                            @if (!$invoice->date_payment)
                                                @if ($invoice->daysDeadline > 7)
                                                    <span class="badge badge-success">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                                    <span class="badge badge-warning">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                   {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @endif
                                            @else
                                                Payée le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Devis</h5>

                    <div class="pull-right">
                        <a href="{{ URL::route('invoice_add_organisation', array('type' => 'D', 'organisation' =>$organisation->id)) }}"
                           class="btn btn-xs btn-default">Ajouter un devis</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Créée le</th>
                            <th>Echéance</th>
                            <th>Montant HT</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($organisation->invoices as $invoice)
                            @if($invoice->type == 'D')
                                <tr>
                                    <td>{{ $invoice->ident }}</td>
                                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($invoice->date_canceled)
                                            <span class="badge badge-danger">Refusé</span>
                                        @else
                                            @if (!$invoice->date_payment)
                                                @if ($invoice->daysDeadline > 7)
                                                    <span class="badge badge-success">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                                    <span class="badge badge-warning">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                   {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @endif
                                            @else
                                                Payée le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

            $('#user_selector').select2();
            $('.datePicker').datepicker();
        });
    </script>
@stop
