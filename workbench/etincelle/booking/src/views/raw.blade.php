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
                            <div class="col-md-6">
                                {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_booking.user_id') ? Session::get('filtre_booking.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
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
                                <th>Utilisateur</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Salle</th>
                                <th>Réservation</th>
                                <th>Facture</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('user_modify', $item->booking->user->id) }}">{{ $item->booking->user->fullname }}</a>
                                        <a href="?filtre_submitted=1&filtre_user_id={{ $item->booking->user->id }}"><i
                                                    class="fa fa-filter"></i></a>
                                    </td>
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
                                                @endif
                                            @endif
                                        @endif
                                    </td>
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
                        {{ $items->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop





@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.datePicker').datepicker();
            $('#filter-client').select2();

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


        });



    </script>
@stop
