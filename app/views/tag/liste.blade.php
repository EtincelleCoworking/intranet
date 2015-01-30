@extends('layouts.master')

@section('meta_title')
    Liste des tags
@stop

@section('content')
    <a href="{{ URL::route('tag_add') }}" class="btn btn-primary pull-right">Ajouter un tag</a>
    <h1>Liste des tags</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Dernière modification</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($tags as $tag)
            <tr>
                <td>{{ $tag->id }}</td>
                <td>
                    <a href="{{ URL::route('tag_modify', $tag->id) }}">{{ $tag->name }}</a>
                </td>
                <td>{{ $tag->updated_at }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">{{ $tags->links() }}</td>
            </tr>
        </tfoot>
    </table>
@stop