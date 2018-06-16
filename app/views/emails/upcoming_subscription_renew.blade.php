@extends('emails.master')

@section('title')
    {{$_ENV['organisation_name']}} - Renouvellement de ton abonnement le {{ date('d/m/Y', strtotime($subscription->renew_at))}}
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                <p>Ton abonnement {{$subscription->kind->shortName}} sera renouvellé automatiquement
                    le {{ date('d/m/Y', strtotime($subscription->renew_at))}}.</p>
                <p>Tu peux modifier ta formule, décaler la date de renouvellement ou le mettre en pause sur la
                    page de gestion de ton abonnement.</p>
            </td>
        </tr>
        <tr>
            <td class="content-block aligncenter">
                <a href="{{ route('invoice_list') }}" class="btn-primary">Gérer mon abonnement</a>
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


