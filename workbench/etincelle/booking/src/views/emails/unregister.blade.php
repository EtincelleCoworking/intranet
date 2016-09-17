@extends('emails.master')

@section('title')
    Désinscription à votre événement
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                Désinscription à votre événement
            </td>
        </tr>
        <tr>
            <td class="content-block">
                <table>
                    <tr>
                        <td width="30%">Utilisateur</td>
                        <td>
                            <strong><a href="mailto:{{$user->email}}">{{$user->fullname}}</a></strong>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">Evénement</td>
                        <td><strong>{{$booking_item->booking->title}}</strong></td>
                    </tr>
                    <tr>
                        <td width="30%">Date</td>
                        <td>
                            <strong>
                                @if($booking_item->start_at instanceof \DateTime)
                                    {{ $booking_item->start_at->format('d/m/Y H:i')}}
                                @else
                                    {{date('d/m/Y H:i', strtotime($booking_item->start_at))}}
                                @endif
                            ({{ durationToHuman($booking_item->duration) }})
                            </strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="content-block">
                Pour toute question ou suggestion, n'hésitez pas à
                <a href="mailto:{{$_ENV['mail_address']}}">nous contacter</a>.
            </td>
        </tr>
    </table>
@stop


