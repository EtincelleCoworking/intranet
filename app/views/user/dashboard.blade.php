@extends('layouts.master')

@section('content')
	<h1>Tableau de bord</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-money fa-2x"></i> <span class="pull-right">CA du mois</span></h3>
                </div>
                <div class="panel-body">
                    <h3 align="center">{{ (($totalMonth) ? $totalMonth->total : 0) }}€</h3>
                </div>
            </div>

            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-file fa-2x"></i> <span class="pull-right">Charges du mois</span></h3>
                </div>
                <div class="panel-body">
                    <h3 align="center">
                        {{ (($chargesMonth) ? $chargesMonth->total : 0) }}€
                        @if ($chargesMonthToPay)
                        <br />
                        dont reste dû : {{ $chargesMonthToPay->total }}€
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>
@stop
