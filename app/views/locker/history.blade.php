@extends('layouts.master')

@section('meta_title')
    Historique du casier {{$locker->name}} ({{$locker->cabinet->name}})
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Historique du casier {{$locker->name}} ({{$locker->cabinet->name}})
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(!count($history))
                        <p>Aucun historique pour ce casier</p>
                    @else
                        <table class="table table-striped table-hover">
                            <thead>
                            <th>Utilisateur</th>
                            <th>Pris le</th>
                            <th>Rendu le</th>
                            </thead>
                            @foreach($history as $item)
                                <tr>
                                    <td class="col-md-6">
                                        <a href="{{ URL::route('user_modify', $item->user->id) }}">{{ $item->user->fullname }}</a>
                                    </td>
                                    <td class="col-md-3">
                                        {{date('d/m/Y H:i', strtotime($item->taken_at))}}
                                    </td>
                                    <td class="col-md-3">
                                        @if($item->released_at)
                                            {{date('d/m/Y H:i', strtotime($item->released_at))}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    <a href="{{URL::route('locker_admin', $locker->cabinet->location_id)}}" class="btn btn-default">Retour à la liste des casiers</a>
                </div>
            </div>
        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {


        });
    </script>
@stop