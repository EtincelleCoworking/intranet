@extends('emails.master')

@section('title')
    Nouvel outil de réservation de salles
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                Vous avez accès à notre outil de gestion de réservation de salles qui était jusqu'à présent accessible à l'adresse
                <a href="https://etincelle.skedda.com/" target="_blank">https://etincelle.skedda.com/</a>
            </td>
        </tr>
        <tr>
            <td class="content-block">
                Cet outil est abandonné au profil d'une solution intégrée à notre intranet qui permettra:
                <ul>
                    <li>Un outil en français</li>
                    <li>Une ergonomie améliorée</li>
                    <li>Une meilleure intégration avec nos autres outils</li>
                    <li>Une meilleure communication interne</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td class="content-block">
                Nous vous avons créé un compte sur l'intranet pour vous permettre d'accéder à cette nouvelle interface
                et y avons réintégré vos éventuelles réservations pour les semaines à venir.
            </td>
        </tr>
        <tr>
            <td class="content-block">
                <table>
                    <tr>
                        <td width="30%">Identifiant</td>
                        <td><strong>{{$user->email}}</strong></td>
                    </tr>
                    <tr>
                        <td>Mot de passe</td>
                        <td>
                            <strong>etincelle</strong>
                            <br /><small><i>(vous pouvez le changer depuis l'interface)</i></small>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="content-block aligncenter">
                <a href="{{ URL::to('booking') }}" class="btn-primary">Me connecter</a>
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


