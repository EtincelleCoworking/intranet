@extends('layouts.master')

@section('meta_title')
    Liste des charges
@stop

@section('content')

    <div class="pull-right">
        <a href="{{ URL::route('charge_list', 'all') }}" class="btn btn-info">Toutes</a>
        <a href="{{ URL::route('charge_list', 'deadline_close') }}" class="btn btn-info">Proches</a>
        <a href="{{ URL::route('charge_list', 'deadline_exceeded') }}" class="btn btn-info">Dépassées</a>
        <a href="{{ URL::route('charge_add') }}" class="btn btn-primary">Ajouter une charge</a>
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
                    <th>Lignes</th>
                    <th>Total HT</th>
                    <th>Total TVA</th>
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
                        @foreach ($charge->items as $item)
                            <div>{{ $item->description }}</div>
                        @endforeach
                    </td>
                    <td>{{ $charge->total }}€</td>
                    <td>{{ $charge->total_vat }}€</td>
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