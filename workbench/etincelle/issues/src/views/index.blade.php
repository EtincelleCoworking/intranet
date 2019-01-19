@extends('layouts.master')

@section('meta_title')
    Tâches
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Tâches</h2>

        </div>
        <div class="col-sm-2">
            <div class="title-action">
                <a href="{{route('issue_create')}}" class="btn btn-primary" id="issue-add">Nouvelle tâche</a>
            </div>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(count($items) == 0)
                        <p>Aucune tâche</p>
                    @else
                        <table class="table">
                            <thead>

                            <tr>
                                <th>Site</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Statut</th>
                                <th>Utilisateur</th>
                                <th>Créé le</th>
                                <th>Mis à jour le</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        @if($item->location)
                                            {{$item->location->full_name}}
                                        @endif
                                    </td>
                                    <td>{{$item->title}}</td>
                                    <td>{{$item->priority}}</td>
                                    <td>{{$item->status}}</td>
                                    <td>
                                        @if (Auth::user()->isSuperAdmin())
                                            <a href="{{ route('user_modify', $item->user->id) }}">{{ $item->user->fullname }}</a>
                                            <a href="?filtre_submitted=1&filtre_user_id={{ $item->user->id }}"><i
                                                        class="fa fa-filter"></i></a>
                                        @else
                                            {{ $item->user->fullname }}
                                        @endif
                                    </td>
                                    <td>{{ date('d/m/Y H:i', strtotime($item->created_at)) }}</td>
                                    <td>{{ date('d/m/Y H:i', strtotime($item->updated_at)) }}</td>
                                    <td>
                                        <a href="{{ route('issue_view', array('id' => $item->id)) }}"
                                          class="btn btn-xs btn-default">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $items->links() }}
                    @endif

                </div>
            </div>
        </div>

    </div>
@stop





@section('stylesheets')
    <style type="text/css">

    </style>
@stop

@section('javascript')
    <script type="text/javascript">

    </script>
@stop



