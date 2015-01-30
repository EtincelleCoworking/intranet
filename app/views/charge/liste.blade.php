@extends('layouts.master')

@section('meta_title')
    Liste des charges
@stop

@section('content')

    <a href="{{ URL::route('charge_add') }}" class="btn btn-primary pull-right">Ajouter une charge</a>

    <h1>Liste des charges</h1>
    @if(count($charges)==0)
        <p>Aucune charge.</p>
    @else
        @foreach ($charges as $charge)
            <div class="panel panel-warning">
                <div class="panel-body">
                    <table class="table">
                        <tbody>
                            <tr class="warning">
                                <td class="col-md-2">{{ date('d/m/Y', strtotime($charge->date_charge)) }}</td>
                                <td class="col-md-5">
                                    @foreach ($charge->tags as $k => $tag)
                                        @if ($k > 0)
                                            ,
                                        @endif
                                        {{ $tag->name }}
                                    @endforeach
                                </td>
                                <td class="col-md-1">{{ $charge->total }}€</td>
                                <td class="col-md-2">{{ (($charge->date_payment) ? 'Payée le '.date('d/m/Y', strtotime($charge->date_payment)) : '') }}</td>
                                <td class="col-md-2">
                                    <a href="{{ URL::route('charge_modify', $charge->id) }}" class="btn btn-xs btn-success">Modifier</a>
                                    @if ($charge->document)
                                        <a href="uploads/charges/{{ $charge->document }}" class="btn btn-xs btn-info" target="_blank"><i class="fa fa-download"></i></a>
                                    @endif
                                    <a href="{{ URL::route('charge_delete', array($charge->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer cette charge ?" rel="nofollow" class="btn btn-xs btn-danger">Retirer</a<
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Montant</th>
                                <th>TVA</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($charge->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td style="text-align:right">{{ $item->amount }}€</td>
                                <td>{{ $item->vat->value }}%</td>
                                <td><a href="{{ URL::route('charge_item_delete', array($charge->id, $item->id)) }}" data-method="delete" data-confirm="Etes-vous certain de vouloir retirer cette ligne ?" rel="nofollow" class="btn btn-xs btn-danger">Retirer</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
            {{ $charges->links() }}
    @endif
@stop