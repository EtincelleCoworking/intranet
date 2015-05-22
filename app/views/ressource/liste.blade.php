@extends('layouts.master')

@section('meta_title')
    Liste des ressources
@stop

@section('content')
    <a href="{{ URL::route('ressource_add') }}" class="btn btn-primary pull-right">Ajouter une ressource</a>
    <h1>Liste des ressources</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Valeur</th>
                <th>Ordre</th>
                <th>Dernière modification</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($ressources as $n => $ressource)
            <tr>
                <td>
                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}">{{ $ressource->name }}</a>
                </td>
                <td align="right">{{ $ressource->amount }} €</td>
                <td>
                    @if ($ressource->order_index > 1)
                    <a href="{{ URL::route('ressource_order_up', $ressource->id) }}"><i class="fa fa-caret-square-o-up"></i></a>
                    @endif
                    {{ $ressource->order_index }}
                    @if ($ressource->order_index < $last)
                    <a href="{{ URL::route('ressource_order_down', $ressource->id) }}"><i class="fa fa-caret-square-o-down"></i></a>
                    @endif
                </td>
                <td>{{ $ressource->updated_at }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">{{ $ressources->links() }}</td>
            </tr>
        </tfoot>
    </table>
@stop