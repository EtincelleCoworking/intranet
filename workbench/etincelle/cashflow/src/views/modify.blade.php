@extends('layouts.master')

@section('meta_title')
    @if (isset($operation))
        Modifier une opération
    @else
        Nouvelle opération
    @endif
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>
                @if (isset($operation))
                    Modifier une opération
                @else
                    Nouvelle opération
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="ibox">
                <div class="ibox-content">
                    @if (isset($operation))
                        {{ Form::model($operation, array('route' => array('cashflow_operation_modify',$operation->account_id,  $operation->id))) }}
                    @else
                        {{ Form::open(array('route' => array('cashflow_operation_add', $account_id))) }}
                        <input type="hidden" name="account_id" value="{{$account_id}}">
                    @endif

                    <div class="row">
                        <div class="col-md-3">
                            {{ Form::label('occurs_at', 'Date') }}
                            <p>{{ Form::text('occurs_at', date('d/m/Y', isset($operation)?strtotime($operation->occurs_at):time()), array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label('name', 'Nom') }}
                            <p>{{ Form::text('name', isset($operation)?$operation->name:'', array('class' => 'form-control')) }}</p>
                            <div class="text-muted">
                                <small>Macros disponibles qui seront remplacées automatiquement:
                                    <ul>
                                        <li>Jour: %today%
                                            (ex. <?php echo CashflowOperation::formatName('%today%', date('Y-m-d')); ?>)
                                        </li>
                                        <li>Semaine: %week%
                                            (ex. <?php echo CashflowOperation::formatName('%week%', date('Y-m-d')); ?>)
                                        </li>
                                        <li>Mois dernier: %month.last%
                                            (ex. <?php echo CashflowOperation::formatName('%month.last%', date('Y-m-d')); ?>)
                                        </li>
                                        <li>Mois courant: %month%
                                            (ex. <?php echo CashflowOperation::formatName('%month%', date('Y-m-d')); ?>)
                                        </li>
                                        <li>Trimestre courant: %quarter%
                                            (ex. <?php echo CashflowOperation::formatName('%quarter%', date('Y-m-d')); ?>)
                                        </li>
                                        <li>Trimestre dernier: %quarter.last%
                                            (ex. <?php echo CashflowOperation::formatName('%quarter.last%', date('Y-m-d')); ?>)
                                        </li>

                                        <li>Année courante: %year%
                                            (ex. <?php echo CashflowOperation::formatName('%year%', date('Y-m-d')); ?>)
                                        </li>
                                    </ul>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('amount', 'Montant') }}
                            <p>{{ Form::text('amount', isset($operation)?$operation->amount:'', array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('frequency', 'Fréquence') }}
                            <p>{{ Form::select('frequency', CashflowOperation::getAvailableFrequencies(), isset($operation)?$operation->frequency:null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('cashflow') }}" class="btn btn-white">Annuler</a>
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
            $('.datePicker').datepicker();
        });
    </script>
@stop