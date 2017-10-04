@extends('layouts.master')

@section('meta_title')
    {{$space}} - {{$period}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>{{$space}} - {{$period}}</h2>
        </div>

    </div>
@stop

@section('content')
    @if(count($items) > 0)
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
        @foreach ($items as $invoice)
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
                        (<a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a>
                        <a href="{{ URL::route('invoice_list') }}?filtre_submitted=1&filtre_user_id={{ $invoice->user->id }}"><i
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
    </table>
@else
    <p>Aucune facture</p>
    @endif
@stop




