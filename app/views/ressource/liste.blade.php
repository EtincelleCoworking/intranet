@extends('layouts.master')

@section('meta_title')
    Liste des ressources
@stop

@section('content')
    <h1>Liste des ressources</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Dernière modification</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($ressources as $ressource)
            <tr>
                <td>{{ $ressource->id }}</td>
                <td>
                    <a href="{{ URL::route('ressource_modify', $ressource->id) }}">{{ $ressource->name }}</a>
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