@extends('layouts.master')

@section('meta_title')
    Liste des factures
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des factures</h2>
        </div>
        <div class="col-sm-4">
            <div class="title-action">
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ URL::route('invoice_add', 'F') }}" class="btn btn-primary">Ajouter une facture</a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Filtre</h5>

                    {{--<div class="ibox-tools">--}}
                    {{--<a class="collapse-link">--}}
                    {{--<i class="fa fa-chevron-up"></i>--}}
                    {{--</a>--}}
                    {{--</div>--}}
                </div>
                <div class="ibox-content">

                    {{ Form::open(array('route' => array('invoice_list'))) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    @if (Auth::user()->isSuperAdmin())
                        <div class="row">
                            <div class="col-md-4">
                                {{ Form::select('filtre_organisation_id', Organisation::SelectAll('Organisation'), Session::get('filtre_invoice.organisation_id') ? Session::get('filtre_invoice.organisation_id') : null, array('id' => 'filter-organisation','class' => 'form-control')) }}
                            </div>
                            <div class="col-md-4">
                                {{ Form::select('filtre_user_id', User::Select('Utilisateur'), Session::get('filtre_invoice.user_id') ? Session::get('filtre_invoice.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                            </div>
                            <div class="col-md-4">
                                {{ Form::select('filtre_location_id', Location::SelectAll('Site', true), Session::get('filtre_invoice.location_id') ? Session::get('filtre_invoice.location_id') : null, array('id' => 'filter-location','class' => 'form-control')) }}
                            </div>
                        </div>
                    @else
                        {{ Form::hidden('filtre_user_id', Auth::user()->id) }}
                    @endif
                    <div class="row">

                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_start', Session::get('filtre_invoice.start') ? date('d/m/Y', strtotime(Session::get('filtre_invoice.start'))) : date('01/12/2014'), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_invoice.end')) ? date('d/m/Y', strtotime(Session::get('filtre_invoice.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-3 input-group-sm">
                            {{ Form::checkbox('filtre_unpaid', true, Session::has('filtre_invoice.filtre_unpaid') ? Session::get('filtre_invoice.filtre_unpaid') : false) }}
                            Impayé
                        </div>
                        <div class="col-md-3">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{URL::route('invoice_filter_reset')}}" class="btn btn-sm btn-default">Réinitialiser</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>






    @if(count($invoices)==0)
        <p>Aucune facture.</p>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Créée le</th>
                                    <th>Client</th>
                                    <th>Echéance</th>
                                    <th>Envoyée le</th>
                                    <th>Montant</th>
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
                                        <td>{{ date('d/m/y', strtotime($invoice->date_invoice)) }}</td>
                                        <td>
                                            @if ($invoice->organisation)
                                                @if (Auth::user()->isSuperAdmin())
                                                    <a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>
                                                @else
                                                    {{ $invoice->organisation->name }}
                                                @endif
                                            @else
                                                {{ preg_replace("/\n.+/", '', $invoice->address) }}
                                            @endif
                                            @if ($invoice->user)
                                                (
                                                <a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a>
                                                <a href="?filtre_submitted=1&filtre_user_id={{ $invoice->user->id }}"><i
                                                            class="fa fa-filter"></i></a>)
                                            @endif
                                        </td>
                                        <td>
                                            @if (!$invoice->date_payment)
                                                @if($invoice->on_hold)
                                                    <span class="badge">En compte</span>
                                                @else
                                                    @if ($invoice->daysDeadline > 7)
                                                        <span class="badge badge-success">
                                    {{ date('d/m', strtotime($invoice->deadline)) }}
                                </span>
                                                    @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                                        <span class="badge badge-warning">
                                    {{ date('d/m', strtotime($invoice->deadline)) }}
                                </span>
                                                    @else
                                                        <span class="badge badge-danger">
                                   {{ date('d/m', strtotime($invoice->deadline)) }}
                                </span>
                                                    @endif
                                                @endif
                                            @else
                                                Payée le {{ date('d/m/y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if (!$invoice->sent_at)
                                                @if (Auth::user()->isSuperAdmin())
                                                    @if (!$invoice->date_payment)
                                                        <a href="{{ URL::route('invoice_send', $invoice->id) }}"
                                                           class="btn btn-xs btn-default btn-outline">
                                                            Envoyer
                                                        </a>
                                                    @endif
                                                @endif
                                            @else
                                                {{ date('d/m/y', strtotime($invoice->sent_at)) }}

                                                @if($invoice->reminder3_at)
                                                    <span class="badge badge-danger">3</span>
                                                @elseif($invoice->reminder2_at)
                                                    <span class="badge badge-danger">2</span>
                                                @elseif($invoice->reminder1_at)
                                                    <span class="badge badge-warning">1</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td style="text-align:right"
                                            title=" {{ Invoice::TotalInvoice($invoice->items) }}€ HT">
                                            {{ Invoice::TotalInvoiceWithTaxes($invoice->items) }}€
                                        </td>
                                        <td>


                                            @if(!$invoice->date_payment)
                                                <form action="{{ URL::route('invoice_stripe', $invoice->id) }}"
                                                      method="POST"
                                                      id="stripe{{$invoice->id}}form">

                                                    <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                                       class="btn btn-xs btn-default"
                                                       target="_blank">PDF</a>
                                                    @if (Auth::user()->isSuperAdmin())
                                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                                           class="btn btn-xs btn-default btn-outline">
                                                            Modifier
                                                        </a>
                                                    @endif
                                                    <input
                                                            type="submit"
                                                            value="Payer par CB"
                                                            class="btn btn-xs btn-default btn-outline"
                                                            id="stripe{{$invoice->id}}"
                                                    />

                                                </form>
                                            @else
                                                <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                                   class="btn btn-xs btn-default"
                                                   target="_blank">PDF</a>
                                                @if (Auth::user()->isSuperAdmin())
                                                    <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                                       class="btn btn-xs btn-default btn-outline">
                                                        Modifier
                                                    </a>
                                                @endif
                                            @endif


                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">{{ $invoices->links() }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@stop

@section('javascript')
    <script src="https://js.stripe.com/v3/"></script>

    <script type="text/javascript">
        $().ready(function () {
            var stripe = Stripe('{{$_ENV['stripe_pk']}}');
            /*
                        var stripeForm = null;
                        var stripeHandler = StripeCheckout.configure({
                            key: '{{$_ENV['stripe_pk']}}',
                token: function (token) {
                    {{--// Use the token to create the charge with a server-side script.--}}
            {{--// You can access the token ID with `token.id`--}}
            stripeForm.append($('<input>').attr({
                type: 'hidden',
                name: 'stripeToken',
                value: token.id
            })).submit();
        }
    });
*/
            <?php
            \Stripe\Stripe::setApiKey($_ENV['stripe_sk']);

?>
            @foreach ($invoices as $invoice)

            @if($invoice->date_payment === null)
            @if($total_amount > 0)
            <?php

            $total_amount = Invoice::TotalInvoiceWithTaxes($invoice->items);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $invoice->user ? $invoice->user->email : '',
                'payment_intent_data' => array(
                    'description' => 'Facture ' . $invoice->ident,
                    'receipt_email' => $invoice->user ? $invoice->user->email : '',
                    'metadata' => array(
                        'invoice_id' => $invoice->id,
                        'user_id' => Auth::user()->id
                    )
                ),
                'line_items' => [[
                    'name' => 'Facture ' . $invoice->ident,
                    'amount' => $total_amount * 100,
                    'currency' => 'eur',
                    'quantity' => 1
                ]],
                'success_url' => URL::route('invoice_list'),
                'cancel_url' => URL::route('invoice_list'),
            ]);
            ?>
            $('#stripe{{$invoice->id}}').on('click', function (e) {
                e.preventDefault();
                stripe.redirectToCheckout({
                    sessionId: '{{$session->id}}'
                }).then(function (result) {
                    // If `redirectToCheckout` fails due to a browser or network
                    // error, display the localized error message to your customer
                    // using `result.error.message`.
                    console.log(result);
                    if (typeof result.error.message !== undefined) {
                        alert(result.error.message);
                    }
                });

            })
            ;
            @endif
            @endif
            @endforeach

            // Close Checkout on page navigation
            $(window).on('popstate', function () {
                handler.close();
            });

            $('.datePicker').datepicker();
            $('#filter-organisation').select2({placeholder: 'Sélectionnez une société', allowClear: true});
            $('#filter-client').select2({placeholder: 'Sélectionnez un client', allowClear: true});
            $('#filter-location').select2({placeholder: 'Sélectionnez un espace', allowClear: true});
        })
        ;
    </script>
@stop
