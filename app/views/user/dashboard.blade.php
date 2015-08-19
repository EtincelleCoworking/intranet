@extends('layouts.master')

@section('content')
    @if (Auth::user()->isSuperAdmin())

        <div class="row">

            <div class="col-lg-9">
                @include('partials.wall.component')
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>CA du mois</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }}
                            €</h1>
                        <small>&nbsp;</small>
                    </div>
                </div>

                @if ($chargesMonth && $chargesMonth->total)
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Dépenses du mois</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">{{ number_format($chargesMonth ? $chargesMonth->total  : 0, 0, ',', '.') }}
                                €</h1>
                            @if ($chargesMonthToPay && $chargesMonthToPay->total)
                                <div class="stat-percent font-bold text-navy">{{ number_format($chargesMonthToPay ? $chargesMonthToPay->total  : 0, 0, ',', '.') }}
                                    €
                                </div>
                                <small>Reste dû</small>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Encours Clients</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ number_format($pending['total'], 0, ',', '.') }}€</h1>
                        <small>En compte: {{ number_format($on_hold['total'], 0, ',', '.') }}€</small>

                    </div>
                </div>

                @include('booking::partials.upcoming_events')
                @include('partials.next_birthday.component')
            </div>

        </div>
    @elseif (Auth::user()->role == 'member')
        <div class="row">
            <div class="col-lg-9">
                @include('partials.wall.component')
            </div>

            <div class="col-lg-3">
                @if($active_subscription)
                    @include('partials.active_subscription', array('active_subscription' => $active_subscription, 'subscription_used' => $subscription_used, 'subscription_ratio' => $subscription_ratio))
                @endif
                @include('booking::partials.upcoming_events')
                @include('partials.next_birthday.component')
            </div>
        </div>
    @endif



@stop


