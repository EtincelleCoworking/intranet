@extends('layouts.master')

@section('meta_title')
    Liste des étiquettes
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des étiquettes</h2>
        </div>
        <div class="col-sm-8">

                <div class="title-action">
                    <a href="{{ URL::route('tag_add') }}" class="btn btn-default">Ajouter une étiquette</a>
                </div>

        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tags as $tag)
                            <tr>
                                <td>
                                    <a href="{{ URL::route('tag_modify', $tag->id) }}">{{ $tag->name }}</a>
                                </td>
                                <td>
                                    <a href="{{ URL::route('tag_modify', $tag->id) }}" class="btn btn-default btn-outline btn-xs">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">{{ $tags->links() }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop