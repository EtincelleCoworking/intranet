@extends('layouts.master')

@section('meta_title')
    Modification d'une réservation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Modification d'une réservation</h2>
        </div>

    </div>
@stop

@section('content')

    <?php


    $ressources = Ressource::whereIsBookable(true)
        ->where('locations.city_id', '=', Auth::user()->location->city_id)
        ->join('locations', 'ressources.location_id', '=', 'locations.id')
        ->with('location')
        ->select('ressources.*')
        ->orderBy('locations.name', 'ASC')
        ->get();

    $ressources_by_location = array();
    $pictures_by_ressource = array();
    foreach ($ressources as $ressource) {
        $location_name = $ressource->location->full_name;
        if (!isset($ressources_by_location[$location_name])) {
            $ressources_by_location[$location_name] = array();
        }
        $ressources_by_location[$location_name][$ressource->id] = sprintf('%s &gt; %s', $location_name, $ressource->name);
        if ($ressource->picture) {
            $pictures_by_ressource[$ressource->id] = URL::asset(sprintf('/etincelle/ressources/%s/%s', $ressource->location->slug, $ressource->picture));
        } else {
            $pictures_by_ressource[$ressource->id] = null;
        }

    }

    ?>


    <div class="row">
        <div class="">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Réservation</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::model($booking_item, array('route' => array('booking_modify_check', $booking_item->id))) }}
                    <div class="row">
                        <div class="col-md-6 col-xs-12">
                            @if (Auth::user()->isSuperAdmin())
                                <div>
                                    {{ Form::label('title', 'Client') }}
                                    <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), $booking_item->booking->user_id, array('id' => 'booking-user','class' => 'form-control')) }}</p>
                                </div>
                                <div>
                                    {{ Form::label('title', 'Organisation') }}
                                    <p>{{ Form::select('organisation_id', Organisation::SelectAll('Sélectionnez une organisation'), $booking_item->booking->organisation_id, array('id' => 'booking-organisation','class' => 'form-control')) }}</p>
                                </div>
                            @endif

                            <div>
                                {{ Form::label('title', 'Titre') }}
                                <p>{{ Form::text('title', $booking_item->booking->title, array('id' => 'booking-title', 'class' => 'form-control')) }}</p>
                            </div>

                            <div>
                                {{ Form::label('description', 'Description') }}
                                <p>{{ Form::textarea('description', $booking_item->booking->content, array('class' => 'form-control')) }}</p>
                            </div>
                            <div>
                                @if($booking_item->confirmed_at)
                                    <p>
                                        <b>Réservation confirmée
                                            le {{ date('d/m/Y', strtotime($booking_item->confirmed_at)) }}
                                            à {{date('H:i', strtotime($booking_item->confirmed_at))}}
                                            par {{$booking_item->confirmedByUser->fullname}}</b>
                                    </p>
                                @endif
                                @if(!$booking_item->confirmed_at || Auth::user()->isSuperAdmin())
                                    <label for="meeting-add-isconfirmed" style="font-weight: normal;">
                                        <p>
                                            {{ Form::checkbox('is_confirmed', true, $booking_item->confirmed_at) }}
                                            Réservation confirmée
                                            <br/>
                                            <span class="text-muted">
                                        <small>Vous pouvez annuler gratuitement jusqu'à 2 jours ouvré avant le début de la réservation tant qu'elle n'est pas confirmée.<br/>
                                        Si nous recevons une autre demande pour le même créneau, nous vous contacterons pour valider ou pas votre réservation.</small>
                                    </span>
                                        </p>
                                    </label>
                                @endif
                            </div>
                            <div>
                                {{ Form::label('participant_count', 'Nombre de participants') }}
                                <p>{{ Form::text('participant_count', $booking_item->participant_count, array('id' => 'booking-participants','class' => 'form-control')) }}</p>
                            </div>

                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="row">
                                <div class="col-xs-12">
                                    {{ Form::label('ressource_id', 'Salle') }}
                                    <p>{{ Form::select('ressource_id', $ressources_by_location, $booking_item->ressource_id, array('id' => 'booking-ressource','class' => 'form-control')) }}</p>
                                </div>

                                <div class="col-xs-4">
                                    {{ Form::label('date', 'Date') }}
                                    <p>{{ Form::text('date', date('d/m/Y', strtotime($booking_item->start_at)), array('class' => 'form-control datePicker', 'id' => 'start_date')) }}</p>
                                </div>
                                <div class="col-xs-4">
                                    {{ Form::label('start', 'Début') }}
                                    <p>{{ Form::select('start', Booking::selectableHours(), date('H:i', strtotime($booking_item->start_at)), array('class' => 'form-control', 'id' => 'start_time')) }}</p>
                                </div>
                                <div class="col-xs-4">
                                    {{ Form::label('end', 'Fin') }}
                                    <p>{{ Form::select('end', Booking::selectableHours(), date('H:i', strtotime($booking_item->start_at) + $booking_item->duration * 60), array('class' => 'form-control', 'id' => 'end_time')) }}</p>
                                </div>
                                {{--
                                                                <div class="col-xs-12">
                                                                    {{ Form::label('layout', 'Configuration') }}
                                                                    <p>{{ Form::select('layout_id', array(), null, array('id' => 'booking-layout','class' => 'form-control')) }}</p>
                                                                </div>
                                --}}

                                <div class="col-xs-12">
                                    <img src="{{$pictures_by_ressource[$booking_item->ressource_id]}}"
                                         class="img-responsive" id="main_picture"/>
                                </div>


                                <?php /*
                            <div class="col-xs-12">
                                <label for="meeting-add-isprivate" style="font-weight: normal;">
                                    <p>
                                        {{ Form::checkbox('is_private', true, $booking_item->booking->is_private, array('id'=> 'meeting-add-isprivate')) }}
                                        Événement privé
                                        <br/>
                                        <span class="text-muted">
                                        <small>Les événements publics sont mis en avant dans la communication (site web et réseaux sociaux)</small>
                                    </span>
                                    </p>
                                </label>
                            </div>
 */ ?>


                            </div>
                        </div>

                    </div>

                    @if (Auth::user()->isSuperAdmin())
                        <div class="row">
                            <div class="hr-line-dashed"></div>
                            <div class="col-xs-6">
                                {{ Form::label('internal_notes', 'Notes internes') }}
                                <p>{{ Form::textarea('internal_notes', $booking_item->internal_notes, array('class' => 'form-control')) }}</p>
                            </div>

                            <div class="col-xs-6">
                                {{ Form::label('sold_price', 'Montant') }}
                                <p>
                                <div class="input-group">
                                    {{ Form::text('sold_price', $booking_item->sold_price, array('class' => 'form-control', 'id' => 'sold_price')) }}
                                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="sold_populate">Calculer</button>
                </span>
                                </div>

                                </p>
                            </div>


                            <div class="col-xs-6">
                                <p>
                                    {{ Form::checkbox('is_free', true, $booking_item->is_free, array('id'=> 'meeting-is-free')) }}
                                    {{ Form::label('meeting-is-free', 'Réservation offerte') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                            <a href="{{ URL::route('booking_with_date', array('now'=> date('Y-m-d', strtotime($booking_item->start_at)))) }}"
                               class="btn btn-white">Annuler</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

@stop

@section('javascript')
    <script type="text/javascript">

        var ressource_pictures = new Array();
        @foreach($pictures_by_ressource as $ressource_id => $uri)
            ressource_pictures[{{$ressource_id}}] = '{{$uri}}';
        @endforeach

        $().ready(function () {

            $('#booking-user').select2();
            $('#booking-organisation').select2();
            $('#booking-ressource')
                .select2()
                .change(function () {
                    console.log(ressource_pictures[$(this).val()]);
                    var uri = ressource_pictures[$(this).val()];
                    if (uri == '') {
                        $('#main_picture').hide();
                    } else {
                        $('#main_picture').attr('src', uri)
                        $('#main_picture').show();
                    }
                });
            $('.datePicker').datepicker();

            var oldOrganisation = $('#oldOrganisation').val();
            var oldContent = '';

            function getListOrganisations(id) {
                var url = "{{ URL::route('user_json_organisations') }}";
                var urlFinale = url.replace("%7Bid%7D", id);

                $('#selectOrganisationId').html('');
                $.getJSON(urlFinale, function (data) {
                    var items = '';
                    $.each(data, function (key, val) {
                        if (oldOrganisation == key) {
                            items = items + '<option value="' + key + '" selected>' + val + '</option>';
                        } else {
                            items = items + '<option value="' + key + '">' + val + '</option>';
                        }
                    });

                    $('#booking-organisation')
                        .html(items)
                        .trigger("change");

                });
            }

            $('#booking-user').on('change', function (e) {
                oldContent = $('#booking-organisation').text();
                getListOrganisations($(this).val());
            });
            $('#booking-organisation').on('change', function (e) {
                if ($('#booking-title').val() == '' || $('#booking-title').val() == oldContent) {
                    $('#booking-title').val($(this).text());
                    oldContent = $(this).text();
                }
            });

            getListOrganisations($('#booking-user').val());

            $('#sold_populate').click(function () {
                $.getJSON('{{URL::route('booking_sold_price')}}', {
                    ressource_id: $('#booking-ressource').val(),
                    occurs_at: $('#start_date').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val()
                }, function (data) {
                    $('#sold_price').val(data.amount);
                });
                return false;
            })

        });
    </script>
@stop
