@extends('layouts.master')

@section('meta_title')
    Liste des devis
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-6">
            <h2>Liste des devis</h2>
        </div>
        <div class="col-sm-6">
            <div class="title-action">
                @if ($filtre == 'canceled')
                    <a href="{{ URL::route('quote_list', 'all') }}" class="btn btn-white">Afficher les devis actifs</a>
                @else
                    <a href="{{ URL::route('quote_list', 'canceled') }}" class="btn btn-white">Afficher les devis
                        refusés</a>
                @endif
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ URL::route('invoice_add', 'D') }}" class="btn btn-default">Ajouter un devis</a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        @if(count($invoices)==0)
                            <p>Aucun devis.</p>
                        @else
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Créée le</th>
                                    <th>Client</th>
                                    <th>Echéance</th>
                                    <th>Montant HT</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->ident }}</td>
                                        <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($invoice->organisation)
                                                @if (Auth::user()->isSuperAdmin())
                                                    <a href="{{ URL::route('organisation_modify', $invoice->organisation->id) }}">{{ $invoice->organisation->name }}</a>
                                                    (
                                                    <a href="{{ URL::route('user_modify', $invoice->user->id) }}">{{ $invoice->user->fullname }}</a>
                                                    )
                                                @else
                                                    {{ $invoice->organisation->name }}
                                                @endif
                                            @else
                                                {{ preg_replace("/\n.+/", '', $invoice->address) }}
                                            @endif
                                        </td>
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
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                @endif
                                            @endif
                                        </td>
                                        <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
                                        <td>
                                                @if (Auth::user()->isSuperAdmin())
                                            <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                               class="btn btn-xs btn-default btn-outline">
                                                    Modifier
                                            </a>
                                                @endif
                                            <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                               class="btn btn-xs btn-default btn-outline" target="_blank">PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="6">{{ $invoices->links() }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>





@stop