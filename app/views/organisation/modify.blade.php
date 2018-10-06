@extends('layouts.master')

@section('meta_title')
    Modification de la société {{$organisation->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Modification de la société {{$organisation->name}}</h2>
        </div>

    </div>
@stop

@section('content')

    <div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-infos"> Société</a></li>
            <li class=""><a data-toggle="tab" href="#tab-rules"> Conditions commerciales</a></li>
            <li class=""><a data-toggle="tab" href="#tab-quotes"> Devis</a></li>
            <li class=""><a data-toggle="tab" href="#tab-invoices"> Factures</a></li>
            <li class=""><a data-toggle="tab" href="#tab-users"> Utilisateurs</a></li>
        </ul>
        <div class="tab-content">
            <div id="tab-infos" class="tab-pane active">
                <div class="panel-body">

                    {{ Form::model($organisation, array('route' => array('organisation_modify', $organisation->id))) }}
                    <div class="row">
                        <div class="col-md-8">

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
                                    <p>{{ Form::select('country_id', Country::Select(), $organisation->country_id, array('class' => 'form-control')) }}</p>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    {{ Form::label('accountant_id', 'Contact facturation') }}
                                    <p>{{ Form::select('accountant_id', User::SelectInOrganisation($organisation->id, ''), null, array('class' => 'form-control')) }}</p>
                                </div>
                                <div class="col-md-6">
                                    {{ Form::label('tva_number', 'TVA') }}
                                    <p>{{ Form::text('tva_number', null, array('class' => 'form-control')) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div>
                                {{ Form::label('domiciliation_kind_id', 'Domiciliation') }}
                                <p>{{ Form::select('domiciliation_kind_id', DomiciliationKind::select(), null, array('class' => 'form-control')) }}</p>
                            </div>
                            <div>
                                {{ Form::label('domiciliation_start_at', 'Début') }}
                                <p>{{ Form::text('domiciliation_start_at', $organisation->domiciliation_start_at?date('d/m/Y', strtotime($organisation->domiciliation_start_at)):null, array('class' => 'form-control datePicker')) }}</p>
                            </div>
                            <div>
                                {{ Form::label('domiciliation_end_at', 'Fin') }}
                                <p>{{ Form::text('domiciliation_end_at', $organisation->domiciliation_end_at?date('d/m/Y', strtotime($organisation->domiciliation_end_at)):null, array('class' => 'form-control datePicker')) }}</p>
                            </div>
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
            <div id="tab-rules" class="tab-pane">
                <div class="panel-body">
                    <?php $options = InvoicingRuleProcessor::getAvailableItems(); ?>
                    @if(count($organisation->rules))
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Devis</th>
                                <th>Facture</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($organisation->rules as $rule)
                                <tr>
                                    <td>
                                        <?php
                                        $processor = $rule->createProcessor();
                                        echo $processor->getCaption();
                                        unset($options[$rule->kind]);
                                        ?>
                                    </td>
                                    <td>
                                        @if($processor->isValidForQuotes())
                                            <i class="fa fa-check"></i>
                                        @else
                                            <i class="fa fa-times"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($processor->isValidForInvoices())
                                            <i class="fa fa-check"></i>
                                        @else
                                            <i class="fa fa-times"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('organisation_rule_delete', array($organisation->id, $rule->id)) }}"
                                           class="btn btn-danger btn-xs btn-outline"
                                           rel="nofollow">Supprimer</a>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>Aucune condition n'est définie</p>
                    @endif
                    @if(count($options) > 0)
                        {{ Form::model($organisation, array('route' => array('organisation_add_rule', $organisation->id))) }}
                        <table>
                            <tr>
                                <td>{{ Form::select('kind', $options, null, array('class' => 'form-control', 'id' => 'rule_selectorrule_selector')) }}</td>
                                <td>&nbsp;</td>
                                <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-default')) }}</td>
                            </tr>
                            {{ Form::close() }}
                        </table>
                    @endif
                </div>
            </div>
            <div id="tab-quotes" class="tab-pane">
                <div class="panel-body">
                    <div class="pull-right">
                        <a href="{{ URL::route('invoice_add_organisation', array('type' => 'D', 'organisation' =>$organisation->id)) }}"
                           class="btn btn-primary">Ajouter un devis</a>
                    </div>
                    @if(count($quotes))
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
                            @foreach ($quotes as $invoice)
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
                                                Payée
                                                le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}
                                        €
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>Aucun devis.</p>
                    @endif
                </div>
            </div>
            <div id="tab-invoices" class="tab-pane">
                <div class="panel-body">
                    <div class="pull-right">
                        @if(count($invoices))
                            <a href="{{ URL::route('organisation_remind', array('id' => $organisation->id)) }}"
                               class="btn btn-default">Relancer</a>
                        @endif
                        <a href="{{ URL::route('invoice_add_organisation', array('type' => 'F', 'organisation' =>$organisation->id)) }}"
                           class="btn btn-primary">Ajouter une facture</a>
                    </div>
                    @if(count($invoices))
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
                            @foreach ($invoices as $invoice)
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
                                                Payée
                                                le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}
                                        €
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>Aucune facture.</p>
                    @endif
                </div>
            </div>
            <div id="tab-users" class="tab-pane">
                <div class="panel-body">
                    <div class="col-sm-6">
                        @if(count($organisation->users))
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
                                        <td>
                                            @if($user->is_hidden_member)
                                                <i class="fa fa-user-secret"></i>
                                            @endif
                                            <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a>
                                            @if($organisation->accountant_id == $user->id)
                                                <i class="fa fa-credit-card" title="Contact facturation"></i>
                                            @endif
                                            @if($user->free_coworking_time)
                                                <i class="fa fa-gift" title="Coworking offert"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('organisation_delete_user', array($organisation->id, $user->id)) }}"
                                               data-confirm="Etes-vous certain de vouloir retirer {{ $user->fullname }} ?"
                                               class="btn btn-danger btn-xs btn-outline"
                                               rel="nofollow">Supprimer</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                            {{ Form::close() }}
                        @else
                            <p>Aucun utilisateur</p>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <table>
                            <tr>
                                <td>{{ Form::select('user_id', User::SelectNotInOrganisation($organisation->id, 'Sélectionnez un utilisateur'), null, array('class' => 'form-control', 'id' => 'user_selector')) }}</td>
                                <td>&nbsp;</td>
                                <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-primary')) }}</td>
                            </tr>
                        </table>

                        {{ Form::model($organisation, array('route' => array('organisation_add_users', $organisation->id))) }}
                        {{ Form::label('content', 'Ajout rapide d\'utilisateurs') }}
                        <p class="text-muted">Ajouter rapidement des utilisateurs et les
                            associer à cette organisation. <br/>Format (1 par ligne): [prénom] [nom] &lt;[email]&gt;</p>
                        {{ Form::textarea('content', null, array('class' => 'form-control')) }}
                        {{ Form::submit('Ajouter', array('class' => 'btn btn-primary')) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

            $('#rule_selector').select2();
            $('.datePicker').datepicker();

            $('.nav-tabs a[href=#tab-users]').on('shown.bs.tab', function (event) {
                $('#user_selector').select2();
                //var x = $(event.target).text();         // active tab
                //var y = $(event.relatedTarget).text();  // previous tab
            });
        });
    </script>
@stop
