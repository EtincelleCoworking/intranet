@extends('layouts.master')

@section('meta_title')
    Liste des charges
@stop

@section('content')

    <div class="pull-right">
        {{ Form::open(array('route' => array('charge_list', $filtre))) }}
            <span class="btn btn-info">
                <div class="col-md-3 input-group-sm">{{ Form::select('type', array('all' => 'Toutes', 'deadline_close' => 'Proches', 'deadline_exceeded' => 'Dépassées'), ((Session::get('filtre_charge.type'))?:'all'), array('class' => 'form-control')) }}</div>
                <div class="col-md-3 input-group-sm">{{ Form::text('filtre_start', ((Session::get('filtre_charge.start')) ? date('d/m/Y', strtotime(Session::get('filtre_charge.start'))) : date('d/m/Y')), array('class' => 'form-control datePicker')) }}</div>
                <div class="col-md-3 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_charge.end')) ? date('d/m/Y', strtotime(Session::get('filtre_charge.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
                <div class="col-md-3">{{ Form::submit('Ok', array('class' => 'btn btn-sm btn-default')) }}</div>
            </span>
        {{ Form::close() }}
        <a href="{{ URL::route('charge_add') }}" class="btn btn-lg btn-primary">Ajouter une charge</a>
    </div>

    <h1>Liste des charges</h1>
    @if(count($charges)==0)
        <p>Aucune charge.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Echéance</th>
                    <th>Tags</th>
                    <th>Société</th>
                    <th>Description</th>
                    <th>Total HT</th>
                    <th>TVA</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($charges as $charge)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($charge->date_charge)) }}</td>
                    <td>
                        @if ($charge->deadline)
                            @if ($charge->daysDeadline > 7 || $charge->date_payment)
                            <span class="badge badge-success">
                                @if ($charge->date_payment)
                                    <i class="fa fa-check"></i>
                                @else
                                    <i class="fa fa-close"></i>
                                @endif
                            @elseif ($charge->daysDeadline <= 7 && $charge->daysDeadline >= 0)
                            <span class="badge badge-warning">
                                <i class="fa fa-close"></i>
                            @else
                            <span class="badge badge-danger">
                                <i class="fa fa-close"></i>
                            @endif
                            {{ date('d/m/Y', strtotime($charge->deadline)) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @foreach ($charge->tags as $k => $tag)
                            @if ($k > 0)
                                ,
                            @endif
                            {{ $tag->name }}
                        @endforeach
                    </td>
                    <td>
                            @if ($charge->organisation)
                                {{$charge->organisation->name}}
                            @else
                                -
                            @endif
                    </td>
                    <td>
                        @foreach ($charge->items as $item)
                            <div>{{ $item->description }}</div>
                        @endforeach
                    </td>
                    <td align="right">{{ $charge->total }}€</td>
                    <td align="right">{{ $charge->total_vat }}€</td>
                    <td>
                        @if ($charge->document)
                            <a href="uploads/charges/{{ $charge->document }}" class="btn btn-xs btn-info" target="_blank"><i class="fa fa-download"></i></a>
                        @endif
                        <div class="pull-right">
                        <a href="{{ URL::route('charge_modify', $charge->id) }}" class="btn btn-xs btn-success">Modifier</a>
                        <a href="{{ URL::route('charge_delete', array($charge->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer cette charge ?" rel="nofollow" class="btn btn-xs btn-danger">Retirer</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
            {{ $charges->links() }}
    @endif
@stop

@section('javascript')
<script type="text/javascript">
$().ready(function(){
  $('.datePicker').datepicker();
    $('.yearDropper').dateDropper({animate_current: false, format: "Y", placeholder: "{{ ((Session::get('filtre_charge.year'))?:date('Y')) }}"});
    $('.monthDropper').dateDropper({animate_current: false, format: "m", placeholder: "{{ ((Session::get('filtre_charge.month'))?:date('m')) }}"});
});
</script>
@stop
