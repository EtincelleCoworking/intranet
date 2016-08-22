@extends('layouts.master')

@section('meta_title')
    Liste des périphériques
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des périphériques</h2>
        </div>
        <div class="col-sm-8">
            <div class="title-action">
                <a href="{{ URL::route('device_add') }}" class="btn btn-default">Ajouter un périphérique</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Membre</th>
                                <th>Mac</th>
                                <th>Nom</th>
                                <th>Vu le</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td>
                                        @if($device->user)
                                            <a href="{{ URL::route('user_modify', $device->user->id) }}">{{ $device->user->fullname }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $device->mac }}</td>
                                    <td>{{ $device->name }}</td>
                                    <td>
                                        @if($device->last_seen_at)
                                            {{ date('d/m/Y H:i', strtotime($device->last_seen_at)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('device_modify', $device->id) }}"
                                           class="btn btn-default btn-xs btn-outline">Modifier</a>
                                        <a href="{{ URL::route('device_delete', $device->id) }}"
                                           class="btn btn-danger btn-xs btn-outline">Supprimer</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="5">{{ $devices->links() }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop