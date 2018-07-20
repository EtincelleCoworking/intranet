@extends('layouts.master')
<?php

$target_year = substr($target_period, 0, 4);
?>
@section('content')

    @if (Auth::user()->isSuperAdmin())

        <div class="row">
            <div class="col-lg-12">
                <?php
                for ($i = 11; $i >= 0; $i--) {
                    $when = strtotime(sprintf('-%d month', $i));
                    $period = date('Y-m', $when);
                    $activeStr = ($target_period == $period) ? ' btn-primary' : ' btn-default';
                    printf('<a href="%s" class="btn btn-xs%s">%s</a>' . "\n", URL::route('admin_dashboard', array('target_period' => $period)), $activeStr, date('m/Y', $when));
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4"></div>
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4"></div>
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4">
                @include('partials.pending.component', array('target_period' => $target_period))
            </div>
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4">
                @include('partials.sales.yearly.component', array('target_period' => $target_period))
            </div>
        </div>
        <div class="row">
            <?php
            $target_days = substr($target_period, 0, 4) . substr($target_period, 5, 2);
            $totalMonth = DB::table('invoices_items')->join('invoices', function ($join) use ($target_days) {
                if (Auth::user()->isSuperAdmin()) {
                    $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                        ->where('invoices.type', '=', 'F')
                        ->where('invoices.days', '=', $target_days);
                } else {
                    $join->on('invoices_items.invoice_id', '=', 'invoices.id')
                        ->where('invoices.type', '=', 'F')
                        ->where('invoices.user_id', '=', Auth::id())
                        ->where('invoices.days', '=', $target_days);
                }
            })->join('ressources', 'ressources.id', '=', 'invoices_items.ressource_id')
                ->where('ressources.ressource_kind_id', '!=', RessourceKind::TYPE_EXCEPTIONNAL)
                ->select(DB::raw('SUM(invoices_items.amount) as total'))->groupBy('invoices.days')->first();

            $totalMonth = $totalMonth ? $totalMonth->total : 0;
            ?>
            <?php
            $costs = Location::getCostPerLocation();
            $total_cost = 0;
            foreach ($costs as $space => $data) {
                $total_cost += $data[$target_period];
            }
            ?>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <?php
                $balance = $totalMonth - $total_cost;
                ?>
                <div class="ibox">
                    <div class="ibox-content"
                         @if ($balance < 0)
                         style="background-color: #f2dede"
                         @else
                         style="background-color: #dff0d8"
                            @endif
                    >
                        <h5>Balance du mois</h5>
                        <h1 class="no-margins"
                            @if ($balance < 0)
                            style="color: #a94442"
                            @else
                            style="color: #3c763d"
                                @endif>
                            {{ number_format( $balance, 0, ',', '.') }} €
                        </h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">

                <div class="ibox">
                    <div class="ibox-content">
                        <h5>CA du mois</h5>
                        <h1 class="no-margins">
                            {{ number_format($totalMonth, 0, ',', '.') }}&nbsp;€
                        </h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">

                <div class="ibox">
                    <div class="ibox-content">
                        <h5>Charges du mois</h5>
                        <h1 class="no-margins">
                            {{ number_format($total_cost , 0, ',', '.') }} €
                        </h1>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            @foreach($datas as $location => $location_datas)

                <div class="ibox collapsed">
                    <div class="ibox-title">
                        <h5>{{$location}}
                        </h5>
                        <div class="ibox-tools">
                        <span
                                @if ($location_datas[$target_year][$target_period]['balance'] < 0)
                                class="label label-danger"
                                @else
                                class="label label-primary"
                                @endif
                        >
                                {{ number_format( $location_datas[$target_year][$target_period]['balance'], 0, ',', '.') }}
                            € HT
                            </span>
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <!--
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-wrench"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                            -->
                        </div>

                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="ibox">
                                    <div class="ibox-content"
                                         @if ($location_datas[$target_year][$target_period]['balance'] < 0)
                                         style="background-color: #f2dede"
                                         @else
                                         style="background-color: #dff0d8"
                                            @endif
                                    >
                                        <h5>Balance</h5>
                                        <h1 class="no-margins"
                                            @if ($location_datas[$target_year][$target_period]['balance'] < 0)
                                            style="color: #a94442"
                                            @else
                                            style="color: #3c763d"
                                                @endif
                                        >
                                            {{ number_format( $location_datas[$target_year][$target_period]['balance'], 0, ',', '.') }}
                                            €
                                        </h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="ibox">
                                    <div class="ibox-content">
                                        <h5>Chiffre d'affaire</h5>
                                        <h1 class="no-margins">{{ number_format( $location_datas[$target_year][$target_period]['sales'], 0, ',', '.') }}
                                            €</h1>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="ibox">
                                    <div class="ibox-content">
                                        <h5>Charges</h5>
                                        <h1 class="no-margins">{{ number_format( $location_datas[$target_year][$target_period]['cost'], 0, ',', '.') }}
                                            €</h1>
                                    </div>
                                </div>
                            </div>


                        </div>
                        @if(isset($ressources[$location]['meeting_room']) && (count($ressources[$location]['meeting_room'])>0))
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Location de salle
                                </div>
                                <div class="panel-body">

                                    <div class="row">
                                        <?php
                                        foreach ($ressources[$location]['meeting_room'] as $ressource_id => $ressource_data) {
                                        ?>
                                        <div class="col-lg-4">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    {{ $ressource_data['name'] }}
                                                </div>
                                                <div class="panel-body">
                                                    <h2>{{ number_format($ressource_data['busy_rate'], 0, ',', '.') }}
                                                        %</h2>
                                                    <div class="progress progress-mini">
                                                        @if($ressource_data['ressource_kind_id'] == RessourceKind::TYPE_MEETING_ROOM)
                                                            <div class="progress">
                                                                <div style="width: {{ number_format($ressource_data['busy_rate'], 0, ',', '.') }}%"
                                                                     aria-valuemax="100" aria-valuemin="0"
                                                                     aria-valuenow="{{ number_format($ressource_data['busy_rate'], 0, ',', '') }}"
                                                                     role="progressbar" class="progress-bar
@if($ressource_data['busy_rate'] > 60)
                                                                        progress-bar-primary
@elseif($ressource_data['busy_rate'] > 30)
                                                                        progress-bar-warning
@else
                                                                        progress-bar-danger
@endif
                                                                        "><span class="sr-only">{{ number_format($ressource_data['busy_rate'], 0, ',', '.') }}
                                                                        %</span>
                                                                    {{ number_format($ressource_data['busy_rate'], 0, ',', '.') }}
                                                                    %
                                                                </div>
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    {{ number_format($ressource_data['amount'], 0, ',', '.') }}€ HT
                                                </div>


                                            </div>


                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(isset($ressources[$location]['private_office']) && (count($ressources[$location]['private_office'])>0))
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Bureaux privatifs
                                </div>
                                <div class="panel-body">

                                    <div class="row">
                                        @foreach($ressources[$location]['private_office'] as $stats)
                                            <div class="col-lg-4">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        {{ $stats['name'] }}
                                                    </div>
                                                    <div class="panel-body">
                                                        <h2>{{ number_format($stats['amount'], 0, ',', '.') }}€ HT</h2>

                                                    </div>

                                                </div>


                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(isset($ressources[$location]['coworking']) && (count($ressources[$location]['coworking'])>0))
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Coworking
                                </div>
                                <div class="panel-body">
                                    <table class="table">
                                        <?php $position = 1; ?>
                                        @foreach($ressources[$location]['coworking'] as $user)
                                            <tr
                                                    @if($user['instance']->free_coworking_time)
                                                    class="text-muted"
                                                    @elseif($user['instance']->is_hidden_member)
                                                    class="text-warning"
                                                    @endif
                                            >
                                                {{--
                                                <td>
                                                    <a href="{{URL::route('user_profile', $user['instance']->id)}}">
                                                        {{$user['instance']->avatarTag38}}
                                                    </a>
                                                </td>
                                                --}}
                                                <td>{{$position++}}.</td>
                                                <td>
                                                    <?php
                                                    switch ($user['instance']->gender) {
                                                        case 'F':
                                                            echo '<i class="fa fa-female"></i>';
                                                            break;
                                                        case 'M':
                                                            echo '<i class="fa fa-male"></i>';
                                                            break;
                                                        default:
                                                            echo '<i class="fa fa-question"></i>';
                                                    }
                                                    ?>
                                                    <a href="{{ URL::route('user_modify', $user['instance']->id) }}">{{ $user['instance']->fullnameOrga }}</a>
                                                    @if($user['instance']->is_hidden_member)
                                                        <i class="fa fa-user-secret" title="Compte caché"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ number_format($user['hours'], 0, ',', '.') }} heures
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
        @endforeach
    @endif




@stop

@section('javascript')
@stop




