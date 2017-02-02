@extends('layouts.master')

@section('meta_title')
    Trésorerie
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Trésorerie</h2>
        </div>
        <div class="col-sm-2">
            <div class="title-action">

            </div>
        </div>

    </div>
@stop

@section('content')
    @foreach($accounts as $account)
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        {{$account->name}}
                        <a href="{{ URL::route('cashflow_add', $account->id) }}"
                           class="btn btn-xs btn-primary pull-right">Nouvelle
                            opération</a>

                    </div>
                    <div class="ibox-content">
                        <table class="table table-condensed table-striped">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Operations</th>
                                <th>Solde</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($account->getDailyOperations() as $date => $data)
                                <tr>
                                    <td>{{date('d/m/Y', strtotime($date))}}</td>
                                    <td>
                                        <table class="table table-condensed">
                                            @foreach($data['operations'] as $operation)
                                                <tr>
                                                    <td>
                                                        @if ($operation['id'])
                                                            <a href="{{ URL::route('cashflow_delete', $operation['id']) }}"><i
                                                                        class="fa fa-close text-danger"></i></a>
                                                            <a href="{{ URL::route('cashflow_modify', $operation['id']) }}">{{$operation['name']}}</a>

                                                            @if($operation['refreshable'])
                                                                <a href="{{ URL::route('cashflow_refresh', $operation['id']) }}"><i
                                                                            class="fa fa-refresh text-success"></i></a>
                                                            @endif
                                                        @else
                                                            {{$operation['name']}}
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if ($operation['amount'] < 0)
                                                            <span style="color: red">{{ number_format( $operation['amount'], 2, ',', '.') }}
                                                                €</span>
                                                        @else
                                                            <span style="color: green">{{ number_format( $operation['amount'], 2, ',', '.') }}
                                                                €</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                    <td class="text-right">
                                        @if ($data['amount'] < 0)
                                            <span style="color: red">{{ number_format( $data['amount'], 0, ',', '.') }}
                                                €</span>
                                        @else
                                            <span style="color: green">{{ number_format( $data['amount'], 0, ',', '.') }}
                                                €</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    @endforeach
@stop





@section('stylesheets')

    <style type="text/css">

    </style>

@stop

@section('javascript')

    <script type="text/javascript">

    </script>

@stop



