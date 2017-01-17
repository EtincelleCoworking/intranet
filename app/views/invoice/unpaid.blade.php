@extends('layouts.master')

@section('meta_title')
    Impayés
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Impayés</h2>
        </div>
        {{--<div class="col-sm-4">--}}
        {{--<div class="title-action">--}}
        {{--@if (Auth::user()->isSuperAdmin())--}}
        {{--<a href="{{ URL::route('invoice_add', 'F') }}" class="btn btn-primary">Ajouter une facture</a>--}}
        {{--@endif--}}
        {{--</div>--}}
        {{--</div>--}}
    </div>
@stop

@section('content')
    @if(count($items)==0)
        <p>Aucune facture impayée.</p>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Nb de factures</th>
                                    <th>1<sup>ère</sup> émise le</th>
                                    <th>Dernière relance</th>
                                    <th>Montant TTC</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $item)
                                    <tr
                                    @if(strtotime('now') - strtotime($item->older_invoice_at) < 31*24*3600)
                                        class="text-muted"
                                    @elseif(strtotime('now') - strtotime($item->older_invoice_at) < 2 * 31*24*3600)
                                    class=""
                                    @elseif(strtotime('now') - strtotime($item->older_invoice_at) < 3 * 31*24*3600)
                                    class="text-warning"
                                    @else
                                        class="text-danger"
                                    @endif
                                    >
                                        <td>
                                            <a href="{{ URL::route('organisation_modify', $item->organisation_id) }}">{{ $item->name }}</a>
                                        </td>
                                        <td>{{ $item->nb_invoices }}</td>
                                        <td>{{ date('d/m/y', strtotime($item->older_invoice_at)) }}</td>
                                        <td>
                                            @if($item->last_invoice_reminder_at)
                                                {{ date('d/m/y', strtotime($item->last_invoice_reminder_at)) }}
                                            @else
                                                -
                                            @endif

                                        </td>
                                        <td style="text-align:right"
                                            title=" {{ number_format( $item->total_ht, 2, ',', '.') }}€ HT">
                                            {{ number_format( $item->total_ttc, 2, ',', '.') }}€
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('organisation_remind', $item->organisation_id) }}"
                                               class="btn btn-xs btn-default">
                                                Relancer
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop
