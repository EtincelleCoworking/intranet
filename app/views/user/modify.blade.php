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
        <div class="col-sm-12">
            <h2>
                @if(Auth::id() == $user->id)
                    Mon profil
                @else
                    Modification de {{ $user->fullname }}
                @endif
            </h2>
        </div>
    </div>
@stop

@section('content')
    {{ Form::model($user, array('route' => array('user_modify', $user->id), 'files' => true)) }}
    <div class="row">
        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Etat civil</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            {{ Form::label('firstname', 'Prénom') }}
                            <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                            {{ Form::label('lastname', 'Nom') }}
                            <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                            {{ Form::label('birthday', 'Date de naissance') }}
                            <p>{{ Form::text('birthday', ($user->birthday == '0000-00-00')?'':date('d/m/Y', strtotime($user->birthday)), array('class' => 'form-control datePicker')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <img alt="{{$user->fullname}}" class="img-circle img-responsive" src="{{$user->avatarUrl}}"/>
                            {{Form::file('avatar')}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Contact</h5>
                </div>

                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <i class="fa fa-phone"></i>
                            {{ Form::label('phone', 'Téléphone') }}
                            <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-envelope"></i>
                            {{ Form::label('email', 'Adresse email') }}
                            <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-lg-6">
                            <i class="fa fa-globe"></i>
                            {{ Form::label('website', 'Site web') }}
                            <small class="text-muted">ex : http://www.coworking-toulouse.com</small>
                            <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                        </div>
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
                            <i class="fa fa-instagram"></i>
                            {{ Form::label('social_instagram', 'Instagram') }}
                            <small class="text-muted">ex : https://instagram.com/etincelle_tls/</small>
                            <p>{{ Form::text('social_instagram', null, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-lg-6">
                            <i class="fa fa-linkedin"></i>
                            {{ Form::label('social_linkedin', 'LinkedIn') }}
                            <p>{{ Form::text('social_linkedin', null, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-lg-6">
                            <i class="fa fa-facebook"></i>
                            {{ Form::label('social_facebook', 'Facebook') }}
                            <p>{{ Form::text('social_facebook', null, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>


                </div>
            </div>


        </div>

        <div class="col-lg-6">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Présentation</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::label('bio_short', 'Métier') }}
                    <p>{{Form::text('bio_short', null, array('class' => 'form-control')) }}</p>
                    {{ Form::label('bio_long', 'Présentation') }}
                    <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
                </div>
            </div>
            @if(Auth::user()->isSuperAdmin())
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Configuration</h5>
                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-lg-6">
                                {{ Form::label('password', 'Mot de passe') }}
                                <p>{{ Form::password('password', array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-lg-6">
                                {{ Form::checkbox('is_member', true) }}
                                {{ Form::label('is_member', 'Membre') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
    <div class="row">
        <div class="col-lg-12">
            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
            <a href="{{ URL::route('user_list') }}" class="btn btn-white">Annuler</a>
        </div>

    </div>
    {{ Form::close() }}
    @if(Auth::user()->isSuperAdmin())
        <div class="hr-line-dashed"></div>
        <div class="row">
            <div class="col-md-6">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Organisations</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
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
                                               href="{{ URL::route('organisation_delete_user', $orga->id, $user->id) }}">Retirer</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td> {{ Form::select('organisation_id', Organisation::SelectNotInOrganisation($user->id, 'Sélectionnez une organisation'), null, array('class' => 'form-control')) }}</td>
                                    <td>{{ Form::submit('Ajouter', array('class' => 'btn btn-info')) }}</td>
                                </tr>
                                </tfoot>
                            </table>


                        </div>
                        {{ Form::close() }}
                    </div>


                </div>
            </div>

            <div class="col-md-6">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Factures</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
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
                                @foreach ($user->invoices as $invoice)
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
                                        <td style="text-align:right">{{ Invoice::TotalInvoice($invoice->items) }}€</td>
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


                </div>
            </div>
        </div>
    @endif

@stop


@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
        });
    </script>
@stop
