@extends('layouts.master')

@section('meta_title')
    Consommations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Consommations</h2>
        </div>
        <div class="col-sm-8">
            @if (Auth::user()->role == 'superadmin')
                <div class="title-action">
                    <a href="{{ URL::route('pasttime_add') }}" class="btn btn-default">Ajouter</a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox collapsed">
                <div class="ibox-title">
                    <h5>Filtre
                    </h5>

                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        {{ Form::open(array('route' => array('pasttime_list'))) }}
                        {{ Form::hidden('filtre_submitted', 1) }}
                        @if (Auth::user()->role == 'superadmin')
                            <div class="col-md-4">
                                {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_pasttime.user_id') ? Session::get('filtre_pasttime.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                            </div>
                        @else
                            {{ Form::hidden('filtre_user_id', Auth::user()->id) }}
                        @endif

                        <div class="col-md-2 input-group-sm">{{ Form::text('filtre_start', Session::get('filtre_pasttime.start') ? date('d/m/Y', strtotime(Session::get('filtre_pasttime.start'))) : date('01/m/Y'), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-2 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_pasttime.end')) ? date('d/m/Y', strtotime(Session::get('filtre_pasttime.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-2 input-group-sm">
                            {{ Form::checkbox('filtre_toinvoice', true, Session::has('filtre_pasttime.toinvoice') ? Session::get('filtre_pasttime.toinvoice') : false) }} A facturer

                        </div>
                        <div class="col-md-2">{{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-default')) }}</div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>



    @if(count($times)==0)
        <p>Aucune donnée.</p>
    @else

        @if(count($recap)>0)

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>En attente de facturation {{ number_format($pending_invoice_amount, 0, ',', '.') }}€ HT</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                @foreach ($recap as $r)
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">{{ $r->name }}</h4>
                                            </div>
                                            <div class="panel-body">
                                                <div>
                                                    @if ($r->hours)
                                                        {{ $r->hours.Lang::choice('messages.times_hours', $r->hours) }}
                                                    @endif
                                                    @if ($r->minutes)
                                                        {{ $r->minutes.Lang::choice('messages.times_minutes', $r->minutes) }}
                                                    @endif
                                                </div>
                                                <div>
                                                    @if ($r->amount > 0)
                                                        {{ number_format($r->amount, 0, ',', '.') }}€ HT
                                                        / {{ number_format($r->amount * 1.2, 0, ',', '.') }}€ TTC
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endif


        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">

                    <div class="ibox-content">
                        <div class="row">

                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    @if (Auth::user()->role == 'superadmin')
                                        <th>Utilisateur</th>
                                    @endif
                                    <th>Ressource</th>
                                    <th>Arrivé</th>
                                    <th>Départ</th>
                                    <th>Total</th>
                                    <th>Facture</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($times as $time)
                                    <tr @if ((Auth::user()->role == 'superadmin') and $time->invoice_id) class="text-muted" @endif >
                                        <td>{{ date('d/m/Y', strtotime($time->date_past)) }}</td>
                                        @if (Auth::user()->role == 'superadmin')
                                            <td>{{ $time->user->fullname }}
                                                <a href="?filtre_submitted=1&filtre_user_id={{ $time->user->id }}"><i class="fa fa-filter"></i></a>
                                            </td>
                                        @endif
                                        <td>{{ $time->ressource->name }}</td>
                                        <td>{{ date('H:i', strtotime($time->time_start)) }}</td>
                                        <td>{{ date('H:i', strtotime($time->time_end)) }}</td>
                                        <td>
                                            {{ $time->past_time }}
                                            @if ($time->comment)
                                                <span data-toggle="tooltip" data-placement="left" title="{{ $time->comment }}"><i
                                                            class="fa fa-question-circle"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($time->invoice_id)
                                                @if ((Auth::user()->role == 'superadmin'))
                                                    <a target="_blank" href="{{ URL::route('invoice_print_pdf', $time->invoice->id) }}">{{ $time->invoice->ident }}</a>
                                                @else
                                                {{ $time->invoice->ident }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('pasttime_modify', $time->id) }}"
                                               class="btn btn-xs btn-default btn-outline">Modifier</a>
                                            @if (Auth::user()->role == 'superadmin')<a
                                                    href="{{ URL::route('pasttime_delete', $time->id) }}"
                                                    class="btn btn-xs btn-danger btn-outline" data-method="delete"
                                                    data-confirm="Etes-vous certain de vouloir supprimer cette ligne ?"
                                                    rel="nofollow">Supprimer</a>@endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{ $times->links() }}
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
            $('.yearDropper').dateDropper({
                animate_current: false,
                format: "Y",
                placeholder: "{{ ((Session::get('filtre_pasttime.year'))?:date('Y')) }}"
            });
            $('.monthDropper').dateDropper({
                animate_current: false,
                format: "m",
                placeholder: "{{ ((Session::get('filtre_pasttime.month'))?:date('m')) }}"
            });
            $('#filter-client').select2();
        });
    </script>
@stop
