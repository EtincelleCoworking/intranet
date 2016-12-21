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
                                <th>Suivi</th>
                                <th>Membre</th>
                                <th>Mac</th>
                                <th>IP</th>
                                <th>Nom</th>
                                <th>Vu le</th>
                                <th>Site</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($devices as $device)
                                <tr @if(!$device->tracking_enabled)
                                    class="text-muted"
                                        @endif>
                                    <td>
                                        @if($device->tracking_enabled)
                                            <a href="{{URL::route('device_disable', $device->id)}}"><i
                                                        class="fa fa-check"></i></a>
                                        @else
                                            <a href="{{URL::route('device_enable', $device->id)}}"><i
                                                        class="fa fa-times"></i></a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->user_id)
                                            <a href="{{ URL::route('user_modify', $device->user_id) }}">{{ $device->username }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $device->mac }}</td>
                                    <td>{{ $device->ip }}</td>
                                    <td>
                                        {{ $device->name }}
                                        @if($device->brand)
                                            ({{$device->brand}})
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->last_seen_at)
                                            {{ date('d/m/Y H:i', strtotime($device->last_seen_at)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->location)
                                            {{$device->city}} > {{$device->location}}
                                        @else
                                            {{$device->city}}
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
                                <td colspan="7">{{ $devices->links() }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop