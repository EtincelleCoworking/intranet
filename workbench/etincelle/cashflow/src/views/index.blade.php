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
                        <a href="{{ URL::route('cashflow_operation_add', $account->id) }}"
                           class="btn btn-xs btn-primary pull-right">Nouvelle
                            opération</a>
                    </div>
                    <div class="ibox-content">
                        <div id="chart-{{$account->id}}"></div>


                        {{ Form::model($account, array('route' => array('cashflow_account_modify_check', $account->id))) }}
                        {{ Form::label('amount', 'Solde actuel') }}
                        <div class="input-group">
                            {{ Form::text('amount', isset($account)?$account->amount:'', array('class' => 'form-control')) }}

                            <span class="input-group-btn">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                                </span>
                        </div>
                        {{ Form::close() }}


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
                                                    <td class="col-lg-1">
                                                        @if ($operation instanceof IDeletableBankOperation and $operation->getDeleteLink())
                                                            <a href="{{ $operation->getDeleteLink() }}"
                                                               class="btn btn-xs btn-danger m-xxs"><i
                                                                        class="fa fa-close"></i></a>
                                                        @endif
                                                    </td>
                                                    <td class="col-lg-7">
                                                        @if ($operation instanceof IEditableBankOperation and $operation->getEditLink())
                                                            <a href="{{ $operation->getEditLink() }}">{{$operation->getName()}}</a>
                                                        @else
                                                            {{$operation->getName()}}
                                                        @endif
                                                        @if ($operation->getComment())
                                                            <span class="badge" title="{{$operation->getComment()}}"><i
                                                                        class="fa fa-info"></i></span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right col-lg-2">
                                                        @if ($operation->getAmount() < 0)
                                                            <span style="color: red">{{ number_format( $operation->getAmount(), 2, ',', '.') }}
                                                                €</span>
                                                        @else
                                                            <span style="color: green">{{ number_format( $operation->getAmount(), 2, ',', '.') }}
                                                                €</span>
                                                        @endif
                                                    </td>
                                                    <td class="col-lg-2">
                                                        @if ($operation instanceof IActionsProviderBankOperation)
                                                            <div class="pull-right">
                                                                @foreach($operation->getBankOperationActions() as $action)
                                                                    <a href="{{ $action->getUrl() }}"
                                                                       class="btn btn-xs m-xxs {{$action->getLinkClass()}}"
                                                                       title="{{$action->getHelp()}}"
                                                                       target="{{$action->getTarget()}}"><i
                                                                                class="fa {{$action->getIconClass()}}"></i></a>
                                                                @endforeach
                                                            </div>
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
                                        <p>
                                            <small>
                                                @if($data['negative'] != 0)
                                                    <span style="color: red">{{ number_format( $data['negative'], 0, ',', '.') }}
                                                        €</span>
                                                    @if($data['positive'] != 0)
                                                        /
                                                    @endif
                                                @endif
                                                @if($data['positive'] != 0)
                                                    <span style="color: green">+{{ number_format( $data['positive'], 0, ',', '.') }}
                                                        €</span>
                                                @endif
                                            </small>
                                        </p>
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

    {{ HTML::style('css/plugins/morris/morris-0.4.3.min.css') }}
    <style type="text/css">
    </style>

@stop

@section('javascript')
    {{ HTML::script('js/plugins/morris/raphael-2.1.0.min.js') }}
    {{ HTML::script('js/plugins/morris/morris.js') }}

    <script type="text/javascript">
        @foreach($accounts as $account)

        Morris.Line({
            element: 'chart-{{$account->id}}',
            data: {{json_encode($charts[$account->id])}},
            xkey: 'date',
            ykeys: ['value'],
            resize: true,
            lineWidth: 4,
            labels: ['Solde'],
            lineColors: ['#1ab394'],
            pointSize: 5,
        });

        @endforeach
    </script>

@stop



