@extends('emails.master')

@section('title')
    Changement de mot de passe
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                Vous avez demandé à générer un nouveau mot de passe.
            </td>
        </tr>
        <tr>
            <td class="content-block">
                <a href="{{ URL::to('password/reset', array($token)) }}" class="btn-primary">Créer un nouveau mot de passe</a>
            </td>
        </tr>
        <tr>
            <td class="content-block">
                Ce lien expire dans {{ Config::get('auth.reminder.expire', 60) }} minutes.
            </td>
        </tr>
    </table>
@stop


