@extends('layouts.master')

@section('meta_title')
    Détail coworkers - {{$city->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Détail coworkers - {{$city->name}}</h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
    @if(count($users) > 0)
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Vu le</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)

            <tr
                    @if($user->last_seen_at < date('Y-m-d', strtotime('-3 month')))
                    class="text-muted"
                    @endif
            >
                <td>{{$user->id}}</td>
                <td>{{$user->firstname}} {{$user->lastname}}</td>
                <td>{{date('d/m/Y', strtotime($user->last_seen_at))}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@else
    <p>Aucune facture</p>
    @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
@stop




