@extends('layouts.master')

@section('meta_title')
    @if ($invoice->type == 'F')
        Modification de la facture {{$invoice->ident}}
    @elseif ($invoice->type == 'D')
        Modification du devis {{$invoice->ident}}
    @endif
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>
                @if ($invoice->type == 'F')
                    Modification de la facture {{$invoice->ident}}
                @elseif ($invoice->type == 'D')
                    Modification du devis {{$invoice->ident}}
                @endif
            </h2>
        </div>
        <div class="col-sm-4">
            @if (Auth::user()->isSuperAdmin())
                <div class="title-action">

                    @if ($invoice->type == 'D')
                        <a href="{{ URL::route('invoice_validate', $invoice->id) }}" data-method="get"
                           data-confirm="Etes-vous certain de vouloir passer ce devis en facture ?"
                           rel="nofollow"
                           class="btn btn-success btn-outline">Facturer</a>
                        <a href="{{ URL::route('invoice_cancel', $invoice->id) }}" data-method="get"
                           data-confirm="Etes-vous certain de vouloir passer ce devis en refusé ?"
                           rel="nofollow"
                           class="btn btn-warning btn-outline">Refuser</a>
                        <a href="{{ URL::route('invoice_delete', $invoice->id) }}" data-method="get"
                           data-confirm="Etes-vous certain de vouloir supprimer ce devis ?" rel="nofollow"
                           class="btn btn-danger btn-outline">Supprimer</a>
                    @endif

                    <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}" class="btn btn-default"
                       target="_blank">PDF</a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>
                        @if ($invoice->organisation)
                            {{ $invoice->organisation->name }}
                            &gt;
                            @if($invoice->user)
                                {{ $invoice->user->fullname }}
                            @endif
                        @endif
                    </h5>
                </div>
                <div class="ibox-content">
                    {{ Form::model($invoice, array('route' => array('invoice_modify', $invoice->id))) }}
                    <div class="row">
                        <div class="col-md-6">

                            {{ Form::label('address', 'Adresse de facturation') }}
                            <p>{{ Form::textarea('address', $invoice->address, array('class' => 'form-control', 'rows' => '5')) }}</p>

                            {{ Form::label('details', 'Détails') }}
                            <p>{{ Form::text('details', $invoice->details, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('date_invoice', 'Date de création') }}
                            <p>{{ Form::text('date_invoice', date('d/m/Y', strtotime($invoice->date_invoice)), array('class' => 'form-control datePicker')) }}</p>

                            {{ Form::label('deadline', 'Date d\'expiration') }}
                            <p>{{ Form::text('deadline', date('d/m/Y', strtotime($invoice->deadline)), array('class' => 'form-control datePicker')) }}</p>

                            {{ Form::label('date_payment', 'Date de paiement') }}
                            <p>{{ Form::text('date_payment', (($invoice->date_payment) ? date('d/m/Y', strtotime($invoice->date_payment)) : null), array('class' => 'form-control datePicker')) }}</p>

                            {{ Form::label('sent_at', 'Date d\'envoi') }}
                            <p>{{ Form::text('sent_at', (($invoice->sent_at) ? date('d/m/Y', strtotime($invoice->sent_at)) : null), array('class' => 'form-control datePicker')) }}</p>

                            <p>{{Form::checkbox('on_hold', true, $invoice->on_hold)}} {{ Form::label('on_hold', 'En compte') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('invoice_list', 'all') }}" class="btn btn-white">Annuler</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5>
                        Lignes de la facture
                    </h5>

                </div>
                <div class="ibox-content">
                    {{ Form::model($invoice->items, array('route' => array('invoice_item_modify', $invoice->id), 'autocomplete' => 'off')) }}
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Ordre</th>
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
                                <td class="col-lg-1">{{ Form::number('order_index['.$item->id.']', $item->order_index, array('class' => 'form-control')) }}</td>
                                <td>{{ Form::select('ressource_id['.$item->id.']', Ressource::SelectAll(), $item->ressource_id, array('class' => 'form-control')) }}</td>
                                <td>
                                    {{ Form::textarea('text['.$item->id.']', $item->text, array('rows' => 4, 'class' => 'form-control')) }}
                                    @if(!$item->subscription_hours_quota)
                                        <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                           data-id="{{$item->id}}" data-kind="subscription">+ Abonnement</a>
                                    @endif
                                    @if(!$item->booking_hours)
                                        <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                           data-id="{{$item->id}}" data-kind="booking">+ Pré-réservation</a>
                                    @endif
                                </td>
                                <td>{{ Form::text('amount['.$item->id.']', $item->amount, array('class' => 'form-control')) }}</td>
                                <td>{{ Form::select('vat_types_id['.$item->id.']', VatType::SelectAll(), $item->vat->id, array('class' => 'form-control')) }}</td>
                                <td>
                                    <a href="{{ URL::route('invoice_item_delete', array($invoice->id, $item->id)) }}"
                                       data-method="delete"
                                       data-confirm="Etes-vous certain de vouloir retirer cette ligne ?"
                                       rel="nofollow"
                                       class="btn btn-xs btn-danger btn-outline">Supprimer</a</td>
                            </tr>
                            <tr id="item-option-subscription-{{$item->id}}"
                                @if(!$item->subscription_hours_quota)
                                    class="hide"
                                @endif
                            >
                                <td></td>
                                <td>Abonnement</td>
                                <td colspan="3">
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Utilisateur</label>
                                        <div class="col-sm-10">
                                            {{ Form::select('subscription_user_id['.$item->id.']', User::SelectInOrganisation($invoice->organisation_id, '-'),$item->subscription_user_id, array('class' => 'form-control')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Abonnement</label>
                                        <div class="col-sm-10">
                                            {{ Form::select('subscription_hours_quota['.$item->id.']',SubscriptionKind::SelectOptions(), $item->subscription_hours_quota, array('class' => 'form-control')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Du</label>
                                        <div class="col-sm-10">
                                            {{ Form::text('subscription_from['.$item->id.']', ($item->subscription_from != '0000-00-00 00:00:00') ?date('d/m/Y', strtotime($item->subscription_from)):null, array('class' => 'form-control datePicker')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Au</label>
                                        <div class="col-sm-10">
                                            {{ Form::text('subscription_to['.$item->id.']', ($item->subscription_to != '0000-00-00 00:00:00')?date('d/m/Y', strtotime($item->subscription_to)):null, array('class' => 'form-control datePicker')) }}
                                        </div>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                            <tr id="item-option-booking-{{$item->id}}"
                                @if(!$item->booking_hours)
                                class="hide"
                                    @endif
                            >
                                <td></td>
                                <td>Pré-réservation</td>
                                <td colspan="3">
                                    {{ Form::text('booking_hours['.$item->id.']', $item->booking_hours, array('class' => 'form-control')) }}
                                </td>
                                <td></td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>{{ Form::number('order_index[0]', 1, array('class' => 'form-control')) }}</td>
                            <td>{{ Form::select('ressource_id[0]', Ressource::SelectAll(), null, array('class' => 'form-control')) }}</td>
                            <td>
                                {{ Form::textarea('text[0]', null, array('rows' => 4, 'placeholder' => 'Nouvelle ligne', 'class' => 'form-control')) }}
                                    <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                       data-id="0" data-kind="subscription">+ Abonnement</a>
                                    <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                       data-id="0" data-kind="booking">+ Pré-réservation</a>
                            </td>
                            <td>{{ Form::text('amount[0]', null, array('class' => 'form-control')) }}</td>
                            <td>{{ Form::select('vat_types_id[0]', VatType::SelectAll(), null, array('class' => 'form-control')) }}</td>
                        </tr>
                        <tr id="item-option-subscription-0" class="hide">
                            <td></td>
                            <td>Abonnement</td>
                            <td colspan="3">
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Utilisateur</label>
                                    <div class="col-sm-10">
                                        {{ Form::select('subscription_user_id[0]', User::SelectInOrganisation($invoice->organisation_id, '-'),null, array('class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Abonnement</label>
                                    <div class="col-sm-10">
                                        {{ Form::select('subscription_hours_quota[0]',SubscriptionKind::SelectOptions(), null, array('class' => 'form-control')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Du</label>
                                    <div class="col-sm-10">
                                        {{ Form::text('subscription_from[0]', null, array('class' => 'form-control datePicker')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Au</label>
                                    <div class="col-sm-10">
                                        {{ Form::text('subscription_to[0]', null, array('class' => 'form-control datePicker')) }}
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr id="item-option-booking-0" class="hide">
                            <td></td>
                            <td>Pré-réservation</td>
                            <td colspan="3">
                                {{ Form::text('booking_hours[0]', null, array('class' => 'form-control')) }}
                            </td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('invoice_list', 'all') }}" class="btn btn-white">Annuler</a>
                        </div>
                    </div>
                    {{ Form::close() }}


                </div>
            </div>

        </div>
    </div>

    @if (count($invoice->comments) > 0)
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
        <br/>
    @endif
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Nouveau commentaire</h3>
        </div>
        <div class="panel-body">
            {{ Form::open(array('route' => array('invoice_comment_add', $invoice->id))) }}
            {{ Form::hidden('invoice_id', $invoice->id) }}
            {{ Form::hidden('user_id', Auth::user()->id) }}
            <p>{{ Form::textarea('content', null, array('class' => 'form-control')) }}</p>
            {{ Form::submit('Ajouter', array('class' => 'btn btn-default')) }}
            {{ Form::close() }}
        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();

            $('.action-item-option-toggle').click(function () {
                $(this).hide();
                $('#item-option-' + $(this).attr('data-kind') + '-' + $(this).attr('data-id')).removeClass('hide');
                return false;
            });
        });
    </script>
@stop
