@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    breadcrumb
@stop

@section('content')
    <div class="row">
        @if (Auth::user()->isSuperAdmin())
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-content">
                        Suivi des réservations
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                            <th>Client</th>
                            <th>Créée le</th>
                            <th>Date</th>
                            <th>Salle</th>
                            <th>Confirmée</th>
                            </thead>
                            <tbody>
                            @foreach($bookings as $booking)
                                <tr>
                                    <td>{{$booking->booking->user->fullname}}</td>
                                    <td>{{date('d/m/Y H:i', strtotime($booking->created_at))}}</td>
                                    <td>
                                        {{date('d/m/Y H:i', strtotime($booking->start_at))}}
                                        - {{date('H:i', strtotime($booking->start_at + $booking->duration * 60))}}
                                    </td>
                                    <td>

                                    </td>
                                    <td>
                                        @if($booking->confirmed_at)
                                            {{date('d/m/Y H:i', strtotime($booking->confirmed_at))}}
                                            @if($booking->confirmed_by_user_id)
                                                {{$booking->confirmedByUser->fullname}}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        @endif


    </div>
@stop

@section('javascript')
@stop







