@extends('emails.master')

@section('title')
    {{$_ENV['organisation_name']}} - Relance
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">

                {{ $content }}

            </td>
        </tr>
        <tr>
            <td class="content-block aligncenter">
                <a href="{{ route('invoice_list') }}" class="btn-primary">Régler par carte bancaire</a>
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


