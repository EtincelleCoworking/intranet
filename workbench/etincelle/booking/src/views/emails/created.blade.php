@extends('emails.master')

@section('title')
    Nouvelle réservation de salle
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                La réservation suivante viens d'être créée
            </td>
        </tr>
        @foreach($booking->items as $booking_item)
        <tr>
            <td class="content-block">
                <table>
                    <tr>
                        <td width="30%">Utilisateur</td>
                        <td><strong>{{$booking->user->fullname}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Salle</td>
                        <td><strong>{{$booking_item->ressource->name}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Date</td>
                        <td>
                            <strong>{{date('d/m/Y H:i', strtotime($booking_item->start_at))}}</strong>
                            <br />
                            Durée: {{ durationToHuman($booking_item->duration) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
        <tr>
            <td class="content-block">
                Pour toute question ou suggestion, n'hésitez pas à
                <a href="mailto:sebastien@coworking-toulouse.com">me contacter</a>.
            </td>
        </tr>
    </table>
@stop

