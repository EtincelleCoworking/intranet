@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    breadcrumb
@stop

@section('content')
        @if (Auth::user()->isSuperAdmin())
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
                                    <td>
                                        <a href="{{ URL::route('user_modify', $booking->booking->user->id) }}">{{ $booking->booking->user->fullname }}</a>
                                        @if($booking->booking->user->phone)
                                            <br/><i class="fa fa-phone"></i> {{ $booking->booking->user->phoneFmt}}
                                        @endif
                                    </td>
                                    <td>{{date('d/m/Y H:i', strtotime($booking->created_at))}}</td>
                                    <td>
                                        {{date('d/m/Y H:i', strtotime($booking->start_at))}}
                                        - {{date('H:i', strtotime($booking->start_at) + $booking->duration * 60)}}
                                    </td>
                                    <td>
                                        {{ $booking->ressource->name }}
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
        @endif
@stop

@section('javascript')
@stop







