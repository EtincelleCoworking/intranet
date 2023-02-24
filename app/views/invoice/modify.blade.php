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
                            {{ Form::label('organisation_id', 'Organisation') }}
                            <p>{{ Form::select('organisation_id', Organisation::selectAll(), $invoice->organisation_id, array('id' => 'selectOrganisationId', 'class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('user_id', 'Utilisateur') }}
                            <p>{{ Form::select('user_id', User::SelectInOrganisation($invoice->organisation_id), $invoice->user_id, array('id' => 'selectUserId', 'class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('address', 'Adresse de facturation') }}
                            <p>{{ Form::textarea('address', $invoice->address, array('id' => 'addressInvoice', 'class' => 'form-control', 'rows' => '5')) }}</p>

                            {{ Form::label('details', 'Détails') }}
                            <p>{{ Form::text('details', $invoice->details, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('date_invoice', 'Date de création') }}
                            <p>{{ Form::text('date_invoice', date('d/m/Y', strtotime($invoice->date_invoice)), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>

                            {{ Form::label('sent_at', 'Date d\'envoi') }}
                            <p>{{ Form::text('sent_at', (($invoice->sent_at) ? date('d/m/Y', strtotime($invoice->sent_at)) : null), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>

                            {{ Form::label('deadline', 'Date d\'expiration') }}
                            <p>{{ Form::text('deadline', date('d/m/Y', strtotime($invoice->deadline)), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('expected_payment_at', 'Date de paiement prévue') }}
                            <p>{{ Form::text('expected_payment_at', (($invoice->expected_payment_at) ? date('d/m/Y', strtotime($invoice->expected_payment_at)) : null), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>

                            {{ Form::label('date_payment', 'Date de paiement') }}
                            <p>{{ Form::text('date_payment', (($invoice->date_payment) ? date('d/m/Y', strtotime($invoice->date_payment)) : null), array('class' => 'form-control datePicker', 'autocomplete' => 'new-password')) }}</p>

                            <p>{{Form::checkbox('on_hold', true, $invoice->on_hold)}} {{ Form::label('on_hold', 'En compte') }}</p>
                            <p>{{Form::checkbox('is_lost', true, $invoice->is_lost)}} {{ Form::label('is_lost', 'Créance irrecouvrable') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::label('business_terms', 'Conditions commerciales') }}
                            <p>{{ Form::textarea('business_terms', $invoice->business_terms, array('class' => 'form-control', 'rows' => '5')) }}</p>
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
                    {{ Form::model($invoice->items, array('route' => array('invoice_item_modify', $invoice->id), 'autocomplete' => 'new-password')) }}
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Ordre</th>
                            <th>Ressource</th>
                            <th>Description</th>
                            <th>Montant HT</th>
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
                                    {{ Form::textarea('text['.$item->id.']', $item->text, array('id' => 'text'.$item->id, 'rows' => 4, 'class' => 'form-control')) }}
                                    @if(!$item->subscription_hours_quota)
                                        <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                           data-id="{{$item->id}}" data-kind="subscription">+ Abonnement</a>
                                    @endif
                                    @if(!$item->coworking_pack_item_count)
                                        <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                           data-id="{{$item->id}}" data-kind="prepaid">+ Pack 10 demi journées
                                            coworking</a>
                                    @endif
                                </td>
                                <td>{{ Form::text('amount['.$item->id.']', $item->amount, array('class' => 'form-control')) }}</td>
                                <td>{{ Form::select('vat_types_id['.$item->id.']', VatType::SelectAll(), $item->vat->id, array('class' => 'form-control')) }}</td>
                                <td>
                                    <a href="{{ URL::route('invoice_item_delete', array($invoice->id, $item->id)) }}"
                                       data-method="delete"
                                       data-confirm="Etes-vous certain de vouloir retirer cette ligne ?"
                                       rel="nofollow"
                                       class="btn btn-xs btn-danger btn-outline">Supprimer</a></td>
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
                                            {{ Form::select('subscription_user_id['.$item->id.']', User::SelectInOrganisation($invoice->organisation_id, '-'),$item->subscription_user_id, array('class' => 'form-control', 'id' => 'subscription_user_id'.$item->id, 'onchange' => '$(\'#update_text_coworking'.$item->id.'\').click(); return true;')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Abonnement</label>
                                        <div class="col-sm-10">
                                            {{ Form::select('subscription_hours_quota['.$item->id.']',SubscriptionKind::where('ressource_id', Ressource::TYPE_COWORKING)->SelectOptions(), $item->subscription_hours_quota, array('class' => 'form-control', 'id' => 'subscription_hours_quota'.$item->id, 'onchange' => '$(\'#update_text_coworking'.$item->id.'\').click(); return true;')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Du</label>
                                        <div class="col-sm-10">
                                            {{ Form::text('subscription_from['.$item->id.']', ($item->subscription_from != '0000-00-00 00:00:00') ?date('d/m/Y', strtotime($item->subscription_from)):null, array('class' => 'form-control datePicker', 'id' => 'subscription_from'.$item->id, 'onchange' => 'coworkingStartUpdated('.$item->id.'); return true;')) }}
                                        </div>
                                    </div>
                                    <div class="form-group"><label
                                                class="col-sm-2 control-label">Au</label>
                                        <div class="col-sm-10">
                                            {{ Form::text('subscription_to['.$item->id.']', ($item->subscription_to != '0000-00-00 00:00:00')?date('d/m/Y', strtotime($item->subscription_to)):null, array('class' => 'form-control datePicker', 'id' => 'subscription_to'.$item->id))}}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"></label>
                                            <div class="col-sm-10">
                                                <a href="#" class="btn btn-default btn-xs action-line-coworking"
                                                   id="update_text_coworking{{$item->id}}"
                                                   data-target-id="{{$item->id}}">Mettre à jour le texte</a>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr id="item-option-prepaid-{{$item->id}}"
                                @if(!$item->coworking_pack_item_count)
                                class="hide"
                                    @endif
                            >
                                <td></td>
                                <td>Demi journées coworking - Prépayées</td>
                                <td colspan="3">
                                    {{ Form::text('coworking_pack_item_count['.$item->id.']', $item->coworking_pack_item_count, array('class' => 'form-control')) }}
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
                                {{ Form::textarea('text[0]', null, array('rows' => 4, 'placeholder' => 'Nouvelle ligne', 'class' => 'form-control', 'id' => 'text0')) }}
                                <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                   data-id="0" data-kind="subscription">+ Abonnement</a>
                                <a href="#" class="btn btn-xs btn-default action-item-option-toggle"
                                   data-id="0" data-kind="prepaid">+ Demi journées coworking prépayées</a>
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
                                        {{ Form::select('subscription_user_id[0]', User::SelectInOrganisation($invoice->organisation_id, '-'),null, array('class' => 'form-control', 'id' => 'subscription_user_id0', 'onchange' => '$(\'#update_text_coworking0\').click(); return true;')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Abonnement</label>
                                    <div class="col-sm-10">
                                        {{ Form::select('subscription_hours_quota[0]',SubscriptionKind::SelectOptions(), null, array('class' => 'form-control', 'id' => 'subscription_hours_quota0', 'onchange' => '$(\'#update_text_coworking0\').click(); return true;')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Du</label>
                                    <div class="col-sm-10">
                                        {{ Form::text('subscription_from[0]', null, array('class' => 'form-control datePicker', 'id' => 'subscription_from0', 'onchange' => 'coworkingStartUpdated(0); return true;')) }}
                                    </div>
                                </div>
                                <div class="form-group"><label
                                            class="col-sm-2 control-label">Au</label>
                                    <div class="col-sm-10">
                                        {{ Form::text('subscription_to[0]', null, array('class' => 'form-control datePicker', 'id' => 'subscription_to0')) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <a href="#" class="btn btn-default btn-xs action-line-coworking"
                                               id="update_text_coworking0"
                                               data-target-id="0">Mettre à jour le texte</a>
                                        </div>
                                    </div>

                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr id="item-option-prepaid-0" class="hide">
                            <td></td>
                            <td>Demi journées coworking - prépayées</td>
                            <td colspan="3">
                                {{ Form::text('coworking_pack_item_count[0]', null, array('class' => 'form-control')) }}
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
                            @if($comment->user_id)
                                <h4 class="media-heading">Par {{ $comment->user->fullname }}</h4>
                            @endif
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

        var oldUser;
        var oldOrganisation;

        function getDataOrganisation(id) {
            oldOrganisation = id;

            var url = "{{ URL::route('organisation_json_infos') }}";
            var urlFinale = url.replace("%7Bid%7D", id);

            $.getJSON(urlFinale, function (data) {
                $.each(data, function (key, val) {
                    $('#addressInvoice').html(val);
                });
            });
        }

        function refreshUserList(id) {
            var url = "{{ URL::route('organisation_json_users') }}";
            var urlFinale = url.replace("%7Bid%7D", id);

            oldUser = $('#selectUserId').val();

            $('#selectUserId').html('');
            $.getJSON(urlFinale, function (data) {
                var items = '';
                $.each(data, function (key, val) {
                    if (oldUser == key) {
                        items = items + '<option value="' + key + '" selected>' + val + '</option>';
                    } else {
                        items = items + '<option value="' + key + '">' + val + '</option>';
                    }
                });

                $('#selectUserId')
                    .html(items)
                    .trigger("change");

            });

        }

        function coworkingStartUpdated(line_id) {
            var m = moment($('#subscription_from' + line_id).datepicker('getDate'));
            m.add(1, 'month');
            $('#subscription_to' + line_id).datepicker('setDate', m.toDate());
            coworking_updateText(line_id);
        }

        function coworking_updateText(line_id) {
            var content = $('#subscription_hours_quota' + line_id + ' option:selected').text().replace('%UserName%', $('#subscription_user_id' + line_id + ' option:selected').text())
            content += "<br />\n";
            var datePicker = $('#subscription_to' + line_id);
            var _date_orig = datePicker.datepicker('getDate');
            var _date = datePicker.datepicker('getDate');
            _date.setTime(_date.getTime() - 24 * 60 * 60 * 1000);
            datePicker.datepicker('setDate', _date);
            var to = datePicker.datepicker('getFormattedDate');
            datePicker.datepicker('setDate', _date_orig);
            //$('#subscription_from' + line_id).datepicker('setUTCDate');

            content += "Du %from% au %to%"
                .replace('%from%', $('#subscription_from' + line_id).val())
                .replace('%to%', to)
            ;

            $('#text' + line_id).val(content);
            return false;
        }

        $().ready(function () {


            $('.datePicker').datepicker();

            $('.action-item-option-toggle').click(function () {
                $(this).hide();
                $('#item-option-' + $(this).attr('data-kind') + '-' + $(this).attr('data-id')).removeClass('hide');
                return false;
            });

            $('#selectOrganisationId').select2();
            $('#selectOrganisationId').on('change', function (e) {
                getDataOrganisation($(this).val());
                refreshUserList($(this).val());
            });

            $('#selectUserId').select2();

            $('.action-line-coworking').click(function () {
                coworking_updateText($(this).attr('data-target-id'));
            });

        });
    </script>
@stop
