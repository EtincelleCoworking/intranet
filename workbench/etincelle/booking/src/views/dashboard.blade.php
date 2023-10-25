@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Suivi des réservations</h2>
            <p>Cette page affiche toutes les réservations à venir dans les 45 prochains jours.
            <ul>
                <li>En vert, celles qui sont confirmées,</li>
                <li>en orange celles qui ne le sont pas, mais sont dans plus de 15 jours</li>
                <li>en rouge celles qui ne sont aps confirmées et dans les 15 prochains jours</li>
            </ul>
            </p>
        </div>
    </div>
@stop

@section('content')
    @if (Auth::user()->isSuperAdmin())
        <div class="ibox">
            <div class="ibox-content">
                <table class="table table-bordered table-hover">
                    <thead>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Salle</th>
                    <th>Confirmée</th>
                    <th>Créée le</th>
                    <th>Actions</th>
                    </thead>
                    <tbody>
                    @foreach($bookings as $booking)
                        @if($status = $booking->getConfirmationStatus())
                            <tr class="bg-{{$status}}">
                        @else
                            <tr class="">
                                @endif
                                <td>
                                    <a href="{{ URL::route('user_modify', $booking->booking->user->id) }}">{{ $booking->booking->user->fullname }}</a>
                                    @if($booking->booking->user->phone)
                                        <br/><i class="fa fa-phone"></i> {{ $booking->booking->user->phoneFmt}}
                                    @endif
                                </td>
                                <td>
                                    {{date('d/m/Y', strtotime($booking->start_at))}}
                                    <br/>
                                    {{date('H:i', strtotime($booking->start_at))}}
                                    - {{date('H:i', strtotime($booking->start_at) + $booking->duration * 60)}}
                                </td>
                                <td>
                                    {{ $booking->ressource->name }}
                                </td>
                                <td>
                                    @if($booking->confirmed_at)
                                        {{date('d/m/Y H:i', strtotime($booking->confirmed_at))}}
                                        @if($booking->confirmed_by_user_id)
                                            <small><br/>par {{$booking->confirmedByUser->fullname}}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{date('d/m/Y H:i', strtotime($booking->created_at))}}</td>
                                <td>
                                    <a href="{{ route('booking_modify', array('id' => $booking->id)) }}"
                                       class="btn btn-xs btn-primary">Modifier</a>
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







