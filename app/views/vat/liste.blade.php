@extends('layouts.master')

@section('meta_title')
    Liste des taux de TVA
@stop

@section('content')
    <a href="{{ URL::route('vat_add') }}" class="btn btn-primary pull-right">Ajouter un taux de TVA</a>
    <h1>Liste des taux de TVA</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Dernière modification</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($vats as $vat)
            <tr>
                <td>{{ $vat->id }}</td>
                <td>
                    <a href="{{ URL::route('vat_modify', $vat->id) }}">{{ $vat->value }}</a>
                </td>
                <td>{{ $vat->updated_at }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">{{ $vats->links() }}</td>
            </tr>
        </tfoot>
    </table>
@stop