@extends('emails.master')

@section('title')
    Etincelle Coworking - Facture {{$invoice->ident}}
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">

<p></p>                Nous vous faisons parvenir ci-joint notre facture n°{{$invoice->ident}}
                du {{date('d/m/Y', strtotime($invoice->date_invoice))}} correspondant aux prestations suivantes:
                </p>
                <ul>
                    @foreach ($invoice->items as $item)
                        <li>{{nl2br($item->text)}}</li>
                    @endforeach
                </ul>


                <p>Nous vous en souhaitons bonne réception.</p>

                <p>Dans l’attente de votre aimable règlement par chèque, virement (les informations bancaires sont
                présentes sur la facture) ou carte bancaire, selon votre convenance, nous restons à votre disposition.</p>

            </td>
        </tr>
        <tr>
            <td class="content-block aligncenter">
                <a href="{{ URL::to('invoice_list') }}" class="btn-primary">Régler cette facture par carte bancaire</a>
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


