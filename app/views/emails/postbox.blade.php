@extends('emails.master')

@section('title')
    Domiciliation - {{$organisation->name}}
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                <p>Nous avons réceptionnés à votre attention du courrier en date
                    du {{date('d/m/Y', strtotime($notification->occurs_at))}} dont voici le détail:</p>

                <table>
                    <tr>
                        <th>Type</th>
                        <th>Expéditeur</th>
                        <th>Informations complémentaires</th>
                    </tr>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                {{$item->quantity}}
                                {{$item->kind->name}}
                                @if($item->is_important)
                                    <br/><strong>Recommandé</strong>
                                @endif
                            </td>
                            <td>{{$item->from_name}}</td>
                            <td>{{$item->detais}}</td>
                        </tr>
                    @endforeach
                </table>

                <p>Nous le tenons à votre disposition dans notre espace.</p>
            </td>
        </tr>
        <tr>
            <td class="content-block aligncenter">
                <a href="{{ route('postbox_details', $organisation->id) }}" class="btn-primary">Historique des
                    notifications</a>
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


