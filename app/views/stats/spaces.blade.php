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
            @foreach($datas as $location => $data)
                <li{{ $index?'':' class="active"' }}>
                    <a data-toggle="tab" href="#tab-{{$index}}" id="tab{{$index}}action">{{$location}}</a>
                </li>
                {{--*/ $index++ /*--}}
            @endforeach
        </ul>
        <div class="tab-content">
            {{--*/ $index = 0 /*--}}
            @foreach($datas as $location => $data)
                <div id="tab-{{$index}}" class="tab-pane{{ $index?'':' active' }}">
                    <div class="panel-body">
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
                                    <td style="text-align: right">{{ sprintf('%0.2f', $infos['sales']) }}€</td>
                                    <td style="text-align: right">{{ sprintf('%0.2f', $infos['cost']) }}€</td>
                                    <td style="text-align: right">
                                        @if ($infos['balance'] < 0)
                                            <span style="color: red">{{ sprintf('%0.2f', $infos['balance']) }}€</span>
                                        @else
                                            <span style="color: green">{{ sprintf('%0.2f', $infos['balance']) }}€</span>
                                        @endif
                                    </td>
                                    <?php $cumul += $infos['balance']; ?>
                                    <td style="text-align: right">
                                        @if ($cumul < 0)
                                            <span style="color: red">{{ sprintf('%0.2f', $cumul) }}€</span>
                                        @else
                                            <span style="color: green">{{ sprintf('%0.2f', $cumul) }}€</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                {{--*/ $index++ /*--}}
            @endforeach
        </div>
    </div>


@stop




