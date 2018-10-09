@extends('layouts.master')

@section('meta_title')
    @if(Auth::id() == $user->id)
        Mon profil
    @else
        Modification de {{ $user->fullname }}
    @endif
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>
                @if(Auth::id() == $user->id)
                    Mon profil
                @else
                    Modification de {{ $user->fullname }} &lt;{{ $user->email }}&gt;
                @endif
            </h2>
            <a href="{{ URL::route('user_affiliate', $user->id) }}"
               class="btn btn-xs btn-default">Affiliation</a>
            @if(Auth::user()->isSuperAdmin())
            <a href="{{ URL::route('stats_devices', $user->id) }}"
               class="btn btn-xs btn-default">Présence</a>
            <a href="{{ URL::route('user_gift', $user->id) }}"
               class="btn btn-xs btn-default">Cadeaux</a>
            @endif
            @if($user->is_staff)
                <a href="{{ URL::route('user_signature', $user->id) }}"
                   class="btn btn-xs btn-default">Signature</a>
            @endif
        </div>
        <div class="col-sm-2">
            <div class="title-action">
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{URL::route('user_login_as', $user->id)}}"
                       title="Se connecter en tant que {{$user->fullname}}"
                       class="btn btn-default"><i class="fa fa-user-secret"></i></a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    {{ Form::model($user, array('route' => array('user_modify', $user->id), 'files' => true)) }}
    <div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-name"> Coordonnées</a></li>
            <li class=""><a data-toggle="tab" href="#tab-intro"> Présentation</a></li>
            <li class=""><a data-toggle="tab" href="#tab-internet"> Internet</a></li>
            <li class=""><a data-toggle="tab" href="#tab-password"> Mot de passe</a></li>
            @if(Auth::user()->isSuperAdmin())
                <li class=""><a data-toggle="tab" href="#tab-admin"> Administration</a></li>
            @endif
        </ul>
        <div class="tab-content">
            <div id="tab-name" class="tab-pane active">
                <div class="panel-body">
                    <div class="col-lg-6">
                        {{ Form::label('firstname', 'Prénom') }}
                        <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                        {{ Form::label('lastname', 'Nom') }}
                        <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                        {{ Form::label('birthday', 'Date de naissance') }}
                        <p>{{ Form::text('birthday', ($user->birthday == '0000-00-00')?'':date('d/m/Y', strtotime($user->birthday)), array('class' => 'form-control datePicker')) }}</p>
                        {{ Form::label('gender', 'Genre') }}
                        <p>{{ Form::select('gender', User::getGenders(), $user->gender, array('class' => 'form-control')) }}</p>
                        <i class="fa fa-phone"></i>
                        {{ Form::label('phone', 'Téléphone') }}
                        <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
                        <i class="fa fa-envelope"></i>
                        {{ Form::label('email', 'Adresse email') }}
                        <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                        <i class="fa fa-globe"></i>
                        {{ Form::label('website', 'Site web') }}
                        <small class="text-muted">ex : http://www.coworking-toulouse.com</small>
                        <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                    </div>

                    <div class="col-lg-6">
                        <img alt="{{$user->fullname}}" class="img-circle img-responsive"
                             src="{{$user->avatarUrl}}"/>
                        {{Form::file('avatar')}}
                    </div>

                </div>
            </div>
            <div id="tab-intro" class="tab-pane">
                <div class="panel-body">
                    {{ Form::label('bio_short', 'Métier') }}
                    <p>{{Form::text('bio_short', null, array('class' => 'form-control', 'autocomplete' => 'new-password')) }}</p>
                    {{ Form::label('bio_long', 'Présentation') }}
                    <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
                    {{ Form::label('hashtags', 'Tags') }}
                    <p>{{Form::select('hashtags[]', Hashtag::select(),$user->hashtags->lists('id'), array('class' => 'form-control', 'multiple' => 'multiple', 'id'=>'hashtags')) }}</p>
                </div>
            </div>
            <div id="tab-internet" class="tab-pane">
                <div class="panel-body">

                    <div class="col-lg-6">
                        <i class="fa fa-twitter"></i>
                        {{ Form::label('twitter', 'Twitter') }}
                        <small class="text-muted">ex : etincelle_tls</small>
                        <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-github"></i>
                        {{ Form::label('social_github', 'GitHub') }}
                        <small class="text-muted">ex : https://github.com/EtincelleCoworking</small>
                        <p>{{ Form::text('social_github', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-linkedin"></i>
                        {{ Form::label('social_linkedin', 'LinkedIn') }}
                        <small class="text-muted">ex :
                            https://fr.linkedin.com/pub/sébastien-hordeaux/2/2b9/953
                        </small>
                        <p>{{ Form::text('social_linkedin', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-instagram"></i>
                        {{ Form::label('social_instagram', 'Instagram') }}
                        <small class="text-muted">ex : https://instagram.com/etincelle_tls/</small>
                        <p>{{ Form::text('social_instagram', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-facebook"></i>
                        {{ Form::label('social_facebook', 'Facebook') }}
                        <small class="text-muted">ex : https://www.facebook.com/EtincelleCoworking</small>
                        <p>{{ Form::text('social_facebook', null, array('class' => 'form-control')) }}</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-slack"></i>
                        {{ Form::label('slack_id', 'Slack') }}
                        <small class="text-muted">ex : shordeaux</small>
                        <p>{{ Form::text('slack_id', null, array('class' => 'form-control')) }}</p>
                    </div>
                </div>
            </div>
            <div id="tab-password" class="tab-pane">
                <div class="panel-body">
                    <div class="col-lg-6">
                        {{ Form::label('password', 'Mot de passe') }}
                        {{ Form::password('password', array('class' => 'form-control', 'autocomplete' => 'new-password')) }}
                    </div>

                </div>
            </div>
            @if(Auth::user()->isSuperAdmin())
                <div id="tab-admin" class="tab-pane">
                    <div class="panel-body">
                        <div class="col-lg-6">
                            {{ Form::label('default_location_id', 'Espace habituel') }}
                            {{ Form::select('default_location_id', Location::SelectAll(false), $user->default_location_id, array('class' => 'form-control')) }}
                        </div>
                        <div class="col-lg-12">
                            {{ Form::checkbox('is_member', true) }}
                            {{ Form::label('is_member', 'Membre') }}
                        </div>
                        <div class="col-lg-12">
                            {{ Form::checkbox('is_student', true) }}
                            {{ Form::label('is_student', 'Etudiant') }}
                        </div>
                        <div class="col-lg-12">
                            {{ Form::checkbox('free_coworking_time', true) }}
                            {{ Form::label('free_coworking_time', 'Offrir le temps en coworking') }}
                        </div>
                        <div class="col-lg-12">
                            {{ Form::checkbox('is_hidden_member', true) }}
                            {{ Form::label('is_hidden_member', 'Cacher ce membre dans les notifications coworking') }}
                        </div>
                        <div class="col-lg-12">
                            {{ Form::label('affiliate_user_id', 'Parrain') }}
                            <p>{{ Form::select('affiliate_user_id', User::select('-'), $user->affiliate_user_id, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-lg-12">
                            {{ Form::checkbox('is_enabled', true) }}
                            {{ Form::label('is_enabled', 'Actif') }}
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
    <div class="form-group">
        <div class="hr-line-dashed"></div>
        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
        <a href="{{ URL::route('user_list') }}" class="btn btn-white">Annuler</a>
        <div class="hr-line-dashed"></div>
    </div>
    {{ Form::close() }}

    @if(Auth::user()->isSuperAdmin())
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-organisation">Organisations</a></li>
                <li class=""><a data-toggle="tab" href="#tab-crm">CRM</a></li>
                <li class=""><a data-toggle="tab" href="#tab-quote">Devis</a></li>
                <li class=""><a data-toggle="tab" href="#tab-invoice">Factures</a></li>
                @if(isset($subscription_stats))
                    <li class=""><a data-toggle="tab" href="#tab-subscription">Abonnement</a></li>
                @endif
            </ul>
            <div class="tab-content">
                <div id="tab-organisation" class="tab-pane active">
                    <div class="panel-body">
                        {{ Form::open(array('route' => array('organisation_user_add', $user->id))) }}
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($user->organisations as $orga)
                                <tr>
                                    <td>{{ $orga->name }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-default"
                                           href="{{ URL::route('organisation_modify', $orga->id) }}">Modifier</a>
                                        <a class="btn btn-xs btn-danger"
                                           href="{{ URL::route('organisation_delete_user', array('organisation' => $orga->id,'id'=> $user->id)) }}">Retirer</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td> {{ Form::select('organisation_id', Organisation::SelectNotInOrganisation($user->id, 'Sélectionnez une organisation'), null, array('class' => 'form-control', 'id'=> 'organisation_selector')) }}</td>
                                <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-info')) }}</td>
                            </tr>
                            </tfoot>
                        </table>


                        {{ Form::close() }}
                    </div>
                </div>
                <div id="tab-quote" class="tab-pane">
                    <div class="panel-body">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Créée le</th>
                                <th>Echéance</th>
                                <th>Montant HT</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach (Invoice::QuoteOnly()->where('user_id', $user->id)->orderBy('created_at', 'DESC')->get() as $invoice)
                                <tr>
                                    <td>{{ $invoice->ident }}</td>
                                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($invoice->date_canceled)
                                            <span class="badge badge-danger">Refusé</span>
                                        @else
                                            @if (!$invoice->date_payment)
                                                @if ($invoice->daysDeadline > 7)
                                                    <span class="badge badge-success">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                                    <span class="badge badge-warning">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                   {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @endif
                                            @else
                                                Payée le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="tab-invoice" class="tab-pane">
                    <div class="panel-body">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Créée le</th>
                                <th>Echéance</th>
                                <th>Montant HT</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach (Invoice::invoiceOnly()->where('user_id', $user->id)->orderBy('created_at', 'DESC')->get() as $invoice)
                                <tr>
                                    <td>{{ $invoice->ident }}</td>
                                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($invoice->date_canceled)
                                            <span class="badge badge-danger">Refusé</span>
                                        @else
                                            @if (!$invoice->date_payment)
                                                @if ($invoice->daysDeadline > 7)
                                                    <span class="badge badge-success">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @elseif ($invoice->daysDeadline <= 7 && $invoice->daysDeadline != -1)
                                                    <span class="badge badge-warning">
                                                    {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                   {{ date('d/m/Y', strtotime($invoice->deadline)) }}
                                                </span>
                                                @endif
                                            @else
                                                Payée le {{ date('d/m/Y', strtotime($invoice->date_payment)) }}
                                            @endif
                                        @endif


                                    </td>
                                    <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('invoice_modify', $invoice->id) }}"
                                           class="btn btn-xs btn-default btn-outline">Modifier</a>
                                        <a href="{{ URL::route('invoice_print_pdf', $invoice->id) }}"
                                           class="btn btn-xs btn-default"
                                           target="_blank">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="tab-crm" class="tab-pane">
                    <div class="panel-body">
                        <div class="col-lg-12">
                            {{ Form::checkbox('is_lead', true) }}
                            {{ Form::label('is_lead', 'Est un prospect') }}
                        </div>

                        <div class="col-lg-12">
                            {{ Form::label('lead_status', 'Statut') }}
                            <p>{{ Form::text('lead_status', $user->lead_status, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-12">
                            {{ Form::label('lead_contacted_at', 'Date de contact') }}
                            <p>{{ Form::text('lead_contacted_at', $user->lead_contacted_at?date('d/m/Y', strtotime($user->lead_contacted_at)):'', array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-lg-12">
                            {{ Form::label('lead_toured_at', 'Date de visite') }}
                            <p>{{ Form::text('lead_toured_at', $user->lead_toured_at?date('d/m/Y', strtotime($user->lead_toured_at)):'', array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-lg-12">
                            {{ Form::label('lead_tried_at', 'Date de journée d\'essai') }}
                            <p>{{ Form::text('lead_tried_at', $user->lead_contacted_at?date('d/m/Y', strtotime($user->lead_tried_at)):'', array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-lg-12">
                            {{ Form::label('lead_closed_at', 'Date de cloture') }}
                            <p>{{ Form::text('lead_closed_at', $user->lead_closed_at?date('d/m/Y', strtotime($user->lead_closed_at)):'', array('class' => 'form-control datePicker')) }}</p>
                        </div>
                    </div>
                </div>
                @if(isset($subscription_stats))
                    <div id="tab-subscription" class="tab-pane">
                        <div class="panel-body">
                            @if($subscription_stats)
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Période</th>
                                        <th>Usage</th>
                                        <th>Depassement</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($subscription_stats as $data)
                                        <tr
                                                @if($data->subscription_overuse_managed)
                                                class="success"
                                                @elseif($data->overuse > 20)
                                                class="danger"
                                                @endif
                                        >
                                            <td>
                                                {{date('d/m/Y', strtotime($data->subscription_from ))}}
                                                au {{date('d/m/Y', strtotime('-1 day', strtotime($data->subscription_to)))}}
                                            </td>
                                            <td>
                                                @if($data->hours||$data->minutes)
                                                    @if ($data->hours)
                                                        {{ $data->hours }} h
                                                    @endif
                                                    @if ($data->minutes)
                                                        {{ $data->minutes }} min
                                                    @endif
                                                @else
                                                    0 h
                                                @endif
                                                @if($data->ordered > 0)
                                                    / {{$data->ordered}} h
                                                @else
                                                    / Illimité
                                                @endif
                                                @if($data->ordered > 0)
                                                    <div class="progress progress-mini">
                                                        <div style="width: {{$data->ratio}}%;" class="progress-bar
                                @if($data->ratio > 100)
                                                                progress-bar-danger
@elseif($data->ratio>80)
                                                                progress-bar-warning

@endif
                                                                "></div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td align="right">
                                                @if($data->overuse>0)
                                                    @if($data->overuse > 20)
                                                        <span class="text-danger">
                                        {{$data->overuse}}%
                                                </span>
                                                    @else
                                                        {{$data->overuse}}%
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>Aucun abonnement</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif



@stop


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
            $('#organisation_selector').select2();
            $('#affiliate_user_id').select2();

            $('#hashtags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                width: 'resolve'
            });

            $('.nav-tabs a[href="#tab-intro"]').on('shown.bs.tab', function (event) {
                $('#hashtags').select2({
                    tags: true,
                    tokenSeparators: [',', ' '],
                    width: 'resolve'
                });
            });
        });
    </script>
@stop
