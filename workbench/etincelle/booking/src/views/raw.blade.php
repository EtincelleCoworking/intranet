@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Réservations</h2>
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

                    {{ Form::open(array('route' => array('booking_filter'))) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    <div class="row">
                        <div class="col-md-4">
                            {{ Form::select('filtre_ressource_id', Ressource::bookable('Sélectionnez une salle'), Session::get('filtre_booking.ressource_id') ? Session::get('filtre_booking.ressource_id') : null, array('class' => 'form-control')) }}
                        </div>

                        <div class="col-md-2 input-group-sm">{{ Form::text('filtre_start', Session::get('filtre_booking.start') ? date('d/m/Y', strtotime(Session::get('filtre_booking.start'))) : date('d/m/Y'), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-2 input-group-sm">{{ Form::text('filtre_end', ((Session::get('filtre_booking.end')) ? date('d/m/Y', strtotime(Session::get('filtre_booking.end'))) : date('t', date('m')).'/'.date('m/Y')), array('class' => 'form-control datePicker')) }}</div>
                        <div class="col-md-4">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{route('booking_filter_reset')}}" class="btn btn-sm btn-default">Réinitialiser</a>
                        </div>
                    </div>
                    @if (Auth::user()->isSuperAdmin())
                        <div class="row">
                            <div class="col-md-3">
                                {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_booking.user_id') ? Session::get('filtre_booking.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                            </div>
                            <div class="col-md-3">
                                {{ Form::select('filtre_organisation_id', Organisation::SelectAll('Sélectionnez une organisation'), Session::get('filtre_booking.organisation_id') ? Session::get('filtre_booking.organisation_id') : null, array('id' => 'filter-organisation','class' => 'form-control')) }}
                            </div>

                            <div class="col-md-3 input-group-sm">
                                {{ Form::checkbox('filtre_toinvoice', true, Session::has('filtre_booking.toinvoice') ? Session::get('filtre_booking.toinvoice') : false) }}
                                A facturer
                            </div>
                            @else
                                {{ Form::hidden('filtre_user_id', Auth::user()->id) }}
                            @endif
                            {{ Form::close() }}
                        </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::open(array('route' => array('booking_global_action'))) }}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @if(count($items) == 0)
                        <p>Aucune réservation</p>
                    @else
                        <table class="table">
                            <thead>

                            <tr>
                                @if (Auth::user()->isSuperAdmin())
                                    <th>{{ Form::checkbox('checkall', false, false, array('id' => 'checkall')) }}</th>
                                    <th>Utilisateur</th>
                                @endif
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Salle</th>
                                <th>Réservation</th>
                                <th>Facture</th>
                                @if (Auth::user()->isSuperAdmin())
                                    <th>Prix</th>
                                    <th>Temps passé</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    @if (Auth::user()->isSuperAdmin())
                                        <th>
                                            {{ Form::checkbox('items[]', $item->id, false, array('class' => 'check')) }}
                                        </th>
                                        <td>
                                            <a href="{{ route('user_modify', $item->booking->user->id) }}">{{ $item->booking->user->fullname }}</a>
                                            <a href="?filtre_submitted=1&filtre_user_id={{ $item->booking->user->id }}"><i
                                                        class="fa fa-filter"></i></a>
                                        </td>
                                    @endif
                                    <td>
                                        {{ date('d/m/Y H:i', strtotime($item->start_at)) }} -
                                        {{ date('H:i', strtotime($item->start_at) + 60 * $item->duration) }}
                                    </td>
                                    <td>{{$item->ressource->location->full_name}}</td>
                                    <td>{{$item->ressource->name}}</td>
                                    <td>{{$item->booking->title}}</td>
                                    <td>
                                        @if ($item->invoice_id)
                                            <a target="_blank"
                                               href="{{ route('invoice_print_pdf', array('id' => $item->invoice->id)) }}">{{ $item->invoice->ident }}</a>
                                        @else
                                            @if ($item->is_free)
                                                Offert
                                            @else
                                                @if (Auth::user()->isSuperAdmin())
                                                    <a href="{{ route('booking_make_gift', array('id' => $item->id)) }}"
                                                       class="btn btn-xs btn-default action-booking-make-gift">Offrir</a>
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    @if (Auth::user()->isSuperAdmin())
                                        <td>
                                            @if($item->sold_price)
                                                {{number_format($item->sold_price, 2, ',', '.')}}€HT
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->is_free)
                                                Offert
                                            @else
                                                <?php

                                                $existing_timeslot = PastTime::query()
                                                    ->where('user_id', $item->booking->user_id)
                                                    ->where('ressource_id', $item->ressource_id)
                                                    ->where('date_past', date('Y-m-d', strtotime($item->start_at)))
                                                    ->where('time_start', date('Y-m-d H:i:s', strtotime($item->start_at)))
                                                    ->where('time_end', date('Y-m-d H:i:s', strtotime($item->start_at) + $item->duration * 60))
                                                    ->get()
                                                    ->first();

                                                ?>

                                                @if($existing_timeslot)
                                                    <i class="fa fa-check"></i>
                                                    <a href="{{ route('pasttime_list', array('filtre_submitted' => true, 'filtre_user_id'=>$item->booking->user_id)) }}"><i
                                                                class="fa fa-filter"></i></a>
                                                @else
                                                    <a href="{{ route('booking_log_time_ajax', array('id' => $item->id)) }}"
                                                       class="btn btn-xs btn-default action-log-time">Comptabiliser</a>
                                                @endif
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        <a href="{{ route('booking_modify', array('id' => $item->id)) }}"
                                           class="btn btn-xs btn-primary">Modifier</a>
                                        <a href="{{ route('booking_delete', array('id' => $item->id)) }}"
                                           class="btn btn-xs btn-danger">Supprimer</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if($pagination_count)
                            {{ $items->links() }}
                        @endif
                        @if (Auth::user()->isSuperAdmin())
                            <input type="submit" class="btn btn-default" name="quote"
                                   value="Faire un devis"/>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@stop





@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
            $('#filter-client').select2();
            $('#filter-organisation').select2();

            $('.action-booking-make-gift')
                .click(function () {
                    var link = $(this);
                    $.ajax({
                        dataType: 'json',
                        url: $(this).attr('href'),
                        type: "GET",
                        success: function (data) {
                            if (data.status == 'KO') {
                                toastr.error(data.message);
                            } else {
                                link.parent().html('Offert');
                            }
                        },
                        error: function (data) {
                            // afficher un message générique?
                            toastr.error('Erreur inconnue');
                            $('#BookingDialog').modal('hide');
                        }
                    });
                    return false;
                });

            $('.action-log-time')
                .click(function () {
                    var link = $(this);
                    $.ajax({
                        dataType: 'json',
                        url: $(this).attr('href'),
                        type: "GET",
                        success: function (data) {
                            if (data.status == 'KO') {
                                toastr.error(data.message);
                            } else {
                                link.parent().html('<i class="fa fa-check"></i>');
                            }
                        },
                        error: function (data) {
                            // afficher un message générique?
                            toastr.error('Erreur inconnue');
                            $('#BookingDialog').modal('hide');
                        }
                    });
                    return false;
                });

            $('#checkall').click(function () {
                $('input.check').prop('checked', $(this).prop('checked'));
            });


        });


    </script>
@stop
