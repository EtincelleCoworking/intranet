@extends('layouts.master')

@section('meta_title')
    Modification de la dépense #{{ $charge->id }}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Modification d'une dépense</h2>
        </div>
        <div class="col-sm-8">
            <div class="title-action">
                <a href="{{ URL::route('charge_duplicate', $charge->id) }}" class="btn btn-default" target="_blank">Dupliquer</a>
            </div>
        </div>
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">
                    {{ Form::model($charge, array('route' => array('charge_modify', $charge->id), 'files' => true)) }}
                    <div class="row">
                        <div class="col-md-3">
                            {{ Form::hidden('oldsTags', $tags, array('class' => 'oldsTags'))}}
                            {{ Form::label('date_charge', 'Date de la charge') }}
                            <p>{{ Form::text('date_charge', date('d/m/Y', strtotime($charge->date_charge)), array('class' => 'form-control datePicker')) }}</p>
                            {{ Form::label('date_payment', 'Date du paiement') }}
                            <p>{{ Form::text('date_payment', (($charge->date_payment)?date('d/m/Y', strtotime($charge->date_payment)):null), array('class' => 'form-control datePicker')) }}</p>
                            {{ Form::label('deadline', 'Date d\'échéance') }}
                            <p>{{ Form::text('deadline', (($charge->deadline)?date('d/m/Y', strtotime($charge->deadline)):null), array('class' => 'form-control datePicker')) }}</p>
                            {{ Form::label('', 'Tags') }}
                            <p>
                                @if ($charge->tags)
                                    @foreach ($charge->tags as $k => $tag)
                                        @if ($k > 0)
                                            ,
                                        @endif
                                        {{ $tag->name }}
                                    @endforeach
                                @endif
                            </p>
                            {{ Form::label('tags', 'Ajouter des tags (séparés par ", ")') }}
                            <p>{{ Form::select('tags[]', array(), null, array('class' => 'form-control tagsGet', 'multiple' => 'multiple', 'data-tags' => true)) }}</p>
                            {{ Form::label('organisation_id', 'Société') }}
                            <p>{{ Form::select('organisation_id', Organisation::SelectAll('Sélectionnez une société'), $charge->organisation_id, array('class' => 'form-control organisationGet')) }}</p>
                            {{ Form::label('document', 'Facture jointe') }}
                            <p>{{ Form::file('document', null, array('class' => 'form-control')) }}</p>


                        </div>

                        <div class="col-md-9">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>TVA</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($charge->items as $item)
                                    <tr>
                                        <td>{{ Form::text('description['.$item->id.']', $item->description, array('class' => 'form-control')) }}</td>
                                        <td>{{ Form::text('amount['.$item->id.']', $item->amount, array('class' => 'form-control')) }}</td>
                                        <td>{{ Form::select('vat_types_id['.$item->id.']', VatType::SelectAll(), $item->vat->id, array('class' => 'form-control')) }}</td>
                                        <td>
                                            <a href="{{ URL::route('charge_item_delete', array($charge->id, $item->id)) }}"
                                               data-method="delete"
                                               data-confirm="Etes-vous certain de vouloir retirer cette ligne ?"
                                               rel="nofollow" class="btn btn-sm btn-danger">Retirer</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>{{ Form::text('description[0]', null, array('placeholder' => 'Nouvelle ligne', 'class' => 'form-control')) }}</td>
                                    <td>{{ Form::text('amount[0]', null, array('class' => 'form-control')) }}</td>
                                    <td>{{ Form::select('vat_types_id[0]', VatType::SelectAll(), null, array('class' => 'form-control')) }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                </tfoot>
                            </table>

                            <br/>
                            <table class="table table-striped">
                                <caption>Liste des paiements</caption>
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Détails</th>
                                    <th>Montant</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($charge->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->date_payment }}</td>
                                        <td>{{ $payment->mode }}</td>
                                        <td>{{ $payment->description }}</td>
                                        <td>{{ $payment->amount }}€</td>
                                        <td>
                                            <a href="{{ URL::route('charge_payment_delete', array($charge->id,$payment->id)) }}"
                                               data-method="delete"
                                               data-confirm="Etes-vous certain de vouloir retirer ce paiement ?"
                                               rel="nofollow" class="btn btn-xs btn-danger">Retirer</a</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>{{ Form::text('payment_date[0]', null, array('class' => 'form-control datePicker', 'placeholder' => 'Nouveau paiement')) }}</td>
                                    <td>{{ Form::text('payment_mode[0]', null, array('class' => 'form-control')) }}</td>
                                    <td>{{ Form::text('payment_description[0]', null, array('class' => 'form-control')) }}</td>
                                    <td>{{ Form::text('payment_amount[0]', null, array('class' => 'form-control')) }}</td>
                                    <td></td>
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
        $().ready(function () {
            $.fn.datepicker.defaults.format = "dd/mm/yyyy";
            $('.datePicker').datepicker();
            $('.organisationGet').select2();

        });
    </script>
@stop
