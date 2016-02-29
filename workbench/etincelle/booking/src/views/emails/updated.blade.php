@extends('emails.master')

@section('title')
    Modification de réservation de salle
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                La réservation suivante vient d'être modifiée
            </td>
        </tr>
        <tr>
            <td class="content-block">
                <table>
                    <tr>
                        <td width="30%">Utilisateur</td>
                        <td><strong>{{$booking_item->booking->user->fullname}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Salle</td>
                        <td><strong>{{$booking_item->ressource->name}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Date</td>
                        <td>
                            <strong>
                                @if($old['start_at'] != $new['start_at'])
                                {{date('d/m/Y H:i', strtotime($old['start_at']))}}
                                &raquo;
                                @endif
                                {{date('d/m/Y H:i', strtotime($new['start_at']))}}
                                (
                                @if($old['duration'] != $new['duration'])
                                {{ durationToHuman($old['duration']) }}
                                &raquo;
                                @endif
                                {{ durationToHuman($new['duration']) }}
                                )
                            </strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="content-block">
                Pour toute question ou suggestion, n'hésitez pas à
                <a href="mailto:sebastien@coworking-toulouse.com">me contacter</a>.
            </td>
        </tr>
    </table>
@stop


