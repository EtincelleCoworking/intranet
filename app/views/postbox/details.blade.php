@extends('layouts.master')

@section('meta_title')
    Domiciliation - {{$organisation->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Domiciliation - {{$organisation->name}}</h2>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(count($notifications) == 0)
                        <p>Aucun courrier n'a encore ne vous a encore été notifié via cette interface.</p>
                    @else
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Contenu</th>
                                <th>Notifié par</th>
                                <th>Vu le</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($notifications as $notification)
                                <tr
                                        @if($notification->seen_at)
                                        class="text-muted"
                                        @endif
                                >
                                    <td class="col-md-1">
                                        {{date('d/m/Y', strtotime($notification->occurs_at))}}
                                    </td>
                                    <td class="col-md-7">
                                        <ul>
                                            @foreach($notification->items as $mail)
                                                @if($mail->is_important)
                                                    <li class="text-danger">
                                                        <strong>
                                                            {{$mail->getContentFmt()}}
                                                        </strong>
                                                    </li>
                                                @else
                                                    <li>
                                                        {{$mail->getContentFmt()}}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="col-md-3">
                                        <a href="mailto:{{$notification->reporter->email}}">{{$notification->reporter->fullname}}</a>
                                        <i class="fa fa-envelope"></i>
                                    </td>
                                    <td class="col-md-1">
                                        @if($notification->seen_at)
                                            {{date('d/m/Y', strtotime($notification->seen_at))}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop