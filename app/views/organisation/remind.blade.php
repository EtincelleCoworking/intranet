@extends('layouts.master')

@section('meta_title')
    Relance de la société {{$organisation->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Relance de la société {{$organisation->name}}</h2>
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
                    {{ Form::model($organisation, array('route' => array('organisation_remind_send', $organisation->id))) }}
                    <div class="row">
                        <div class="col-lg-6">
                            <p>{{ Form::textarea('content', $content, array('class' => 'form-control', 'rows' => 15)) }}</p>

                        </div>
                        <div class="col-lg-6">
                            {{$content}}
                            <table class="table">
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{Form::checkbox('invoices[]', $invoice->id, true)}}</td>
                                        <td>
                                            {{ $invoice->ident }}</td>
                                        <td>{{ date('d/m/y', strtotime($invoice->date_invoice)) }}</td>
                                        <td style="text-align:right"
                                            title=" {{ Invoice::TotalInvoice($invoice->items) }}€ HT">
                                            {{ Invoice::TotalInvoiceWithTaxes($invoice->items) }}€
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                               class="btn btn-xs btn-default"
                                               target="_blank">PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                {{ Form::submit('Envoyer', array('class' => 'btn btn-success')) }}
                                <a href="{{ URL::route('invoice_unpaid') }}" class="btn btn-white">Annuler</a>
                            </div>
                        </div>
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

        });
    </script>
@stop
