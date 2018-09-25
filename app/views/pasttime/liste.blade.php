@extends('layouts.master')

@section('meta_title')
    Suivi du temps passé
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Suivi du temps passé</h2>
        </div>
        <div class="col-sm-8">
            <div class="title-action">
                <a href="{{ URL::route('pasttime_add') }}" class="btn btn-primary">Ajouter</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Filtre</h5>

                    {{--<div class="ibox-tools">--}}
                    {{--<a class="collapse-link">--}}
                    {{--<i class="fa fa-chevron-up"></i>--}}
                    {{--</a>--}}
                    {{--</div>--}}
                </div>
                <div class="ibox-content">
                    {{ Form::open(array('route' => array('pasttime_list'))) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    @if (Auth::user()->isSuperAdmin())
                        <div class="row">
                            <div class="col-md-6">
                                {{ Form::select('filtre_organisation_id', Organisation::SelectAll('Sélectionnez une organisation'), Session::get('filtre_pasttime.organisation_id') ? Session::get('filtre_pasttime.organisation_id') : null, array('id' => 'filter-organisation','class' => 'form-control')) }}
                            </div>
                            <div class="col-md-6">
                                {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_pasttime.user_id') ? Session::get('filtre_pasttime.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                            </div>
                        </div>
                    @else
                        {{ Form::hidden('filtre_user_id', Auth::user()->id) }}
                    @endif

                    <div class="row">
                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_start', Session::get('filtre_pasttime.start') ? date('d/m/Y', strtotime(Session::get('filtre_pasttime.start'))) : date('01/m/Y'), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-3 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_pasttime.end')) ? date('d/m/Y', strtotime(Session::get('filtre_pasttime.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
                        @if (Auth::user()->isSuperAdmin())
                            <div class="col-md-3 input-group-sm">
                                {{ Form::checkbox('filtre_toinvoice', true, Session::has('filtre_pasttime.toinvoice') ? Session::get('filtre_pasttime.toinvoice') : false) }}
                                A facturer
                            </div>
                            <div class="col-md-3 input-group-sm">
                                {{ Form::checkbox('filtre_exclude_coworking', true, Session::has('filtre_pasttime.exclude_coworking') ? Session::get('filtre_pasttime.exclude_coworking') : false) }}
                                Exclure le coworking
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{URL::route('pasttime_filter_reset')}}" class="btn btn-sm btn-default">Réinitialiser</a>
                            @if (Auth::user()->isSuperAdmin())
                                {{ Form::submit('A facturer', array('class' => 'btn btn-sm btn-primary', 'name'=>'toinvoice')) }}
                            @endif
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>



    @if(count($recap)>0 or $active_subscription)

        <div class="row">
            <div class="@if($active_subscription) col-lg-8 @else  col-lg-12 @endif">
                @if(count($recap))
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>En attente de facturation {{ number_format($pending_invoice_amount, 0, ',', '.') }}€
                                HT</h5>
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
                                                        {{ $r->hours }} h
                                                    @endif
                                                    @if ($r->minutes)
                                                        {{ $r->minutes }} min
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
                @endif
            </div>
            @if($active_subscription)
                <div class="col-lg-4">
                    @include('partials.active_subscription', array('active_subscription' => $active_subscription, 'subscription_used' => $subscription_used, 'subscription_ratio' => $subscription_ratio))
                </div>
            @endif
        </div>

    @endif
    @if(count($times)==0)
        <p>Aucune donnée.</p>
    @else
        {{ Form::open(array('route' => array('pasttime_global_action'))) }}


        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">

                    <div class="ibox-content">
                        <div class="row">

                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>{{ Form::checkbox('checkall', false, false, array('id' => 'checkall')) }}</th>
                                    <th>Site</th>
                                    <th>Date</th>
                                    @if (Auth::user()->isSuperAdmin())
                                        <th>Utilisateur</th>
                                    @endif
                                    <th>Ressource</th>
                                    <th>Arrivé</th>
                                    <th>Départ</th>
                                    <th>Total</th>
                                    <th>Confirmation</th>
                                    <th>Facture</th>
                                    @if (Auth::user()->isSuperAdmin())
                                        <th>Actions</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($times as $time)
                                    <tr @if ((Auth::user()->isSuperAdmin()) and ($time->invoice_id or $time->is_free)) class="text-muted" @endif >
                                        <th>
                                            @if(!($time->invoice_id or $time->is_free))
                                                {{ Form::checkbox('items[]', $time->id, false, array('class' => 'check')) }}
                                            @endif
                                        </th>
                                        <td>{{ $time->location }}</td>

                                        <td>{{ date('d/m/Y', strtotime($time->date_past)) }}</td>
                                        @if (Auth::user()->isSuperAdmin())
                                            <td>
                                                @if ($time->organisation)
                                                    @if (Auth::user()->isSuperAdmin())
                                                        <a href="{{ URL::route('organisation_modify', $time->organisation->id) }}">{{ $time->organisation->name }}</a>
                                                    @else
                                                        {{ $time->organisation->name }}
                                                    @endif
                                                    @if ($time->user)
                                                        (
                                                        <a href="{{ URL::route('user_modify', $time->user->id) }}">{{ $time->user->fullname }}</a>
                                                        <a href="?filtre_submitted=1&filtre_user_id={{ $time->user->id }}"><i
                                                                    class="fa fa-filter"></i></a>)
                                                    @endif
                                                @else
                                                    @if ($time->user)
                                                        <a href="{{ URL::route('user_modify', $time->user->id) }}">{{ $time->user->fullname }}</a>
                                                        <a href="?filtre_submitted=1&filtre_user_id={{ $time->user->id }}"><i
                                                                    class="fa fa-filter"></i></a>
                                                    @endif
                                                @endif

                                            </td>
                                        @endif
                                        <td>{{ $time->ressource->name }}</td>
                                        <td>{{ date('H:i', strtotime($time->time_start)) }}</td>
                                        <td>
                                            {{ $time->time_end?date('H:i', strtotime($time->time_end)):'-' }}
                                            @if($time->device_id)
                                                <span class="badge"
                                                      title="Mis à jour automatiquement via la détection WIFI">A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $time->past_time }}
                                            @if ($time->comment)
                                                <span data-toggle="tooltip" data-placement="left"
                                                      title="{{ $time->comment }}"><i
                                                            class="fa fa-question-circle"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($time->confirmed)
                                                <i class="fa fa-check"></i>
                                            @else
                                                @if(!($time->invoice_id or $time->is_free) && $time->date_past != date('Y-m-d'))
                                                    <a href="{{ URL::route('pasttime_confirm', $time->id) }}"
                                                       class="ajax btn btn-xs btn-primary">Confirmer</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($time->invoice_id && is_object($time->invoice))
                                                <a target="_blank"
                                                   href="{{ URL::route('invoice_print_pdf', $time->invoice->id) }}">{{ $time->invoice->ident }}</a>
                                            @else
                                                @if ($time->is_free)
                                                    Offert
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        </td>
                                        @if (Auth::user()->isSuperAdmin())<a
                                        <td>
                                            <a href="{{ URL::route('pasttime_modify', $time->id) }}"
                                               class="btn btn-xs btn-default">Modifier</a>
                                            href="{{ URL::route('pasttime_delete', $time->id) }}"
                                            class="btn btn-xs btn-danger" data-method="delete"
                                            data-confirm="Etes-vous certain de vouloir supprimer cette ligne ?"
                                            rel="nofollow">Supprimer</a>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                            {{ $times->links() }}
                            @if (Auth::user()->isSuperAdmin())
                                <input type="submit" class="btn btn-default pull-right" name="invoice"
                                       value="Facturer"/>
                                <input type="submit" class="btn btn-default pull-right" name="gift"
                                       value="Offrir"/>
                            @endif
                            <input type="submit" class="btn btn-default pull-right" name="confirm" value="Confirmer"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{ Form::close() }}
    @endif
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
            $('#filter-client').select2();
            $('#filter-organisation').select2();

            $('#checkall').click(function () {
                $('input.check').prop('checked', $(this).prop('checked'));
            });

            $('.ajax').click(function () {
                $.ajax({
                    url: $(this).attr('href'),
                    context: $(this)
                }).done(function (data) {
                    $(this).parent().html(data);
                });
                return false;
            });


        });
    </script>
@stop
