@extends('emails.master')

@section('title')
    Modification de réservation de salle
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                La réservation suivante viens d'être modifiée
            </td>
        </tr>
        <tr>
            <td class="content-block">
                <table>
                    <tr>
                        <td width="30%">Salle</td>
                        <td><strong>{{$booking_item->ressource->name}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Date</td>
                        <td>
                            @if($old['start_at'] <> $new['start_at'])
                                <strong>
                                    {{date('d/m/Y H:i', strtotime($old['start_at']))}}
                                    &raquo;
                                    {{date('d/m/Y H:i', strtotime($new['start_at']))}}
                                </strong>
                                <br/>
                            @endif
                            @if($old['start_at'] <> $new['start_at'])
                                Durée:
                                {{ durationToHuman($old['duration']) }}
                                &raquo;
                                {{ durationToHuman($new['duration']) }}
                            @endif
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


