@extends('layouts.master')

@section('content')
    @if (Auth::user()->role == 'superadmin')
        <div class="row">
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="m-b-md">CA du mois</h5>

                        <h1 class="no-margins">{{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }}
                            €</h1>
                        <small>&nbsp;</small>
                    </div>
                </div>
            </div>

            @if ($chargesMonth && $chargesMonth->total)
                <div class="col-lg-3">
                    <div class="ibox">
                        <div class="ibox-content">
                            <h5 class="m-b-md">
                                Charges du mois
                            </h5>

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
                </div>
            @endif

            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="m-b-md">Encours Clients</h5>

                        <h1 class="no-margins">{{ number_format($pending['total'], 0, ',', '.') }}€</h1>
                        <small>&nbsp;</small>

                    </div>
                </div>
            </div>


        </div>
    @elseif (Auth::user()->role == 'member')
        <div class="row">
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5 class="m-b-md">CA du mois</h5>

                        <h1 class="no-margins">
                            {{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }} €
                        </h1>
                        <small>&nbsp;</small>
                    </div>
                </div>
            </div>
        </div>
    @endif


@stop
