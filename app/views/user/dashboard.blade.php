@extends('layouts.master')

@section('content')
	<h1>Tableau de bord</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="portlet">
                <h4 class="portlet-title"><u>Stats du mois</u></h4>
                <div class="portlet-body">
                    <table class="table keyvalue-table">
                        <tbody>
                            <tr>
                                <td class="kv-key"><i class="fa fa-dollar kv-icon kv-icon-primary"></i> CA du mois</td>
                                <td class="kv-value">{{ (($totalMonth) ? $totalMonth->total : 0) }}€</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
