@extends('emails.master')

@section('title')
    @if($is_new)
        Nouvelle réservation de salle
    @else
        Modification de réservation de salle
    @endif
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                @if($is_new)
                    La réservation suivante vient d'être créée
                @else
                    La réservation suivante vient d'être modifiée
                @endif
            </td>
        </tr>
        @foreach($booking->items as $booking_item)
            <tr>
                <td class="content-block">
                    <table>
                        <tr>
                            <td width="30%">Titre</td>
                            <td><strong>{{$booking->title}}</strong></td>
                        </tr>
                        <tr>
                            <td width="30%">Description</td>
                            <td>{{\Michelf\Markdown::defaultTransform($booking->content)}}</td>
                        </tr>
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
        @endforeach
        <tr>
            <td class="content-block">
                Pour toute question ou suggestion, n'hésitez pas à
                <a href="mailto:sebastien@coworking-toulouse.com">me contacter</a>.
            </td>
        </tr>
    </table>
@stop


