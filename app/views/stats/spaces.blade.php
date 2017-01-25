@extends('layouts.master')

@section('meta_title')
    Rentabilité par espace
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Rentabilité par espace</h2>
        </div>

    </div>
@stop

@section('content')
    <div class="tabs-container">
        <ul class="nav nav-tabs">
            {{--*/ $index = 0 /*--}}
            <li{{ $index?'':' class="active"' }}>
                <a data-toggle="tab" href="#tab-{{$index}}" id="tab{{$index}}action">Global</a>
            </li>
            {{--*/ $index = 1 /*--}}
            @foreach($datas as $location => $data)
                <li{{ $index?'':' class="active"' }}>
                    <a data-toggle="tab" href="#tab-{{$index}}" id="tab{{$index}}action">{{$location}}</a>
                </li>
                {{--*/ $index++ /*--}}
            @endforeach
        </ul>
        <div class="tab-content">
            {{--*/ $index = 0 /*--}}
            <div id="tab-{{$index}}" class="tab-pane{{ $index?'':' active' }}">
                <div class="panel-body">
                    @foreach($global as $year => $data)
                        <?php $total_sales = 0; ?>
                        <?php $total_costs = 0; ?>
                        <p><strong>{{$year}}</strong></p>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td width="20%">Période</td>
                                <td style="text-align: right" width="20%">Chiffre d'affaires</td>
                                <td style="text-align: right" width="20%">Coût</td>
                                <td style="text-align: right" width="20%">Balance</td>
                                <td style="text-align: right" width="20%">Cumul</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $cumul = 0; ?>
                            @foreach($data as $period => $infos)
                                <?php $total_sales += $infos['sales']; ?>
                                <?php $total_costs += $infos['cost']; ?>
                                <tr>
                                    <td>{{$period}}</td>
                                    <td style="text-align: right">{{ number_format($infos['sales'], 0, ',', '.') }}€
                                    </td>
                                    <td style="text-align: right">{{ number_format($infos['cost'], 0, ',', '.') }}€</td>
                                    <td style="text-align: right">
                                        @if ($infos['balance'] < 0)
                                            <span style="color: red">{{ number_format($infos['balance'], 0, ',', '.') }}
                                                €</span>
                                        @else
                                            <span style="color: green">{{ number_format( $infos['balance'], 0, ',', '.') }}
                                                €</span>
                                        @endif
                                    </td>
                                    <?php $cumul += $infos['balance']; ?>
                                    <td style="text-align: right">
                                        @if ($cumul < 0)
                                            <span style="color: red">{{ number_format( $cumul, 0, ',', '.') }}€</span>
                                        @else
                                            <span style="color: green">{{ number_format( $cumul, 0, ',', '.') }}€</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                <th style="text-align: right">{{ number_format( $total_sales, 0, ',', '.') }}€
                                </th>
                                <th style="text-align: right">{{ number_format( $total_costs, 0, ',', '.') }}€
                                </th>
                                <th style="text-align: right">
                                    @if ($total_sales - $total_costs < 0)
                                        <span style="color: red">{{ number_format( $total_sales - $total_costs, 0, ',', '.') }}€</span>
                                    @else
                                        <span style="color: green">{{ number_format( $total_sales - $total_costs, 0, ',', '.') }}€</span>
                                    @endif
                                </th>
                                <td style="text-align: right" width="20%"></td>
                            </tr>
                            </tfoot>
                        </table>
                    @endforeach
                </div>
            </div>

            {{--*/ $index++ /*--}}
            @foreach($datas as $location => $data_)
                <div id="tab-{{$index}}" class="tab-pane{{ $index?'':' active' }}">
                    <div class="panel-body">
                        @foreach($data_ as $year => $data)
                            <p><strong>{{$year}}</strong></p>
                            <table class="table">
                                <tr>
                                    <td width="20%">Période</td>
                                    <td style="text-align: right" width="20%">Chiffre d'affaires</td>
                                    <td style="text-align: right" width="20%">Coût</td>
                                    <td style="text-align: right" width="20%">Balance</td>
                                    <td style="text-align: right" width="20%">Cumul</td>
                                </tr>
                                <?php $cumul = 0; ?>
                                @foreach($data as $period => $infos)
                                    <tr>
                                        <td>{{$period}}</td>
                                        <td style="text-align: right">{{ number_format($infos['sales'], 0, ',', '.') }}€
                                        </td>
                                        <td style="text-align: right">{{ number_format($infos['cost'], 0, ',', '.') }}
                                            €
                                        </td>
                                        <td style="text-align: right">
                                            @if ($infos['balance'] < 0)
                                                <span style="color: red">{{ number_format($infos['balance'], 0, ',', '.') }}
                                                    €</span>
                                            @else
                                                <span style="color: green">{{ number_format( $infos['balance'], 0, ',', '.') }}
                                                    €</span>
                                            @endif
                                        </td>
                                        <?php $cumul += $infos['balance']; ?>
                                        <td style="text-align: right">
                                            @if ($cumul < 0)
                                                <span style="color: red">{{ number_format( $cumul, 0, ',', '.') }}
                                                    €</span>
                                            @else
                                                <span style="color: green">{{ number_format( $cumul, 0, ',', '.') }}
                                                    €</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endforeach
                    </div>
                </div>

                {{--*/ $index++ /*--}}
            @endforeach
        </div>
    </div>


@stop




